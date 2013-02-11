<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class Doctrine {

  /**
   * Doctrine\ORM\EntityManager
   */
  private $em = null;

  /**
   * Symfony\Component\Validator\Validation
   */
  private $validator = null;

  /**
   * Doctrine
   */
  public function __construct()
  {
    // load database configuration from CodeIgniter
    require_once APPPATH.'config/database.php';
    require_once APPPATH.'libraries/Doctrine/Symfony/Component/ClassLoader/UniversalClassLoader.php';

    // Symfony2 autoloader for PHP >= 5.3
    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'  => APPPATH.'libraries/Doctrine',
        'Doctrine' => APPPATH.'libraries',
        'Entity'   => APPPATH.'models'
    ));
    $loader->register();

    // Configure ORM
    // Globally used cache driver, in production use APC or memcached
    if(ENVIRONMENT == 'development') {
        // set up simple array caching for development mode
        $cache = new \Doctrine\Common\Cache\ArrayCache;
    } else {
        // set up caching with APC(or memcached) for production mode
        $cache = new \Doctrine\Common\Cache\ApcCache;
    }

    // Standard annotation reader
    $annotationReader       = new Doctrine\Common\Annotations\AnnotationReader;
    $cachedAnnotationReader = new Doctrine\Common\Annotations\CachedReader(
        $annotationReader, // use reader
        $cache // and a cache driver
    );

    // Create a driver chain for metadata reading
    $driverChain = new Doctrine\ORM\Mapping\Driver\DriverChain();
    // load superclass metadata mapping only, into driver chain
    // also registers Gedmo annotations.NOTE: you can personalize it

    // now we want to register our application entities,
    // for that we need another metadata driver used for Entity namespace
    $annotationDriver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
        $cachedAnnotationReader, // our cached annotation reader
        array(APPPATH.'models/Entity') // paths to look in
    );
    // NOTE: driver for application Entity can be different, Yaml, Xml or whatever
    // register annotation driver for our application Entity namespace
    $driverChain->addDriver($annotationDriver, 'Entity');

    // general ORM configuration
    $config = new Doctrine\ORM\Configuration;
    $config->setProxyDir(sys_get_temp_dir());
    $config->setProxyNamespace('Proxy');
    $config->setAutoGenerateProxyClasses(false); // this can be based on production config.
    // register metadata driver
    $config->setMetadataDriverImpl($driverChain);
    // use our allready initialized cache driver
    $config->setMetadataCacheImpl($cache);
    $config->setQueryCacheImpl($cache);

    // For use @ORM on entities files.
    AnnotationRegistry::registerLoader(function($class) use ($loader) {
        $loader->loadClass($class);
        return class_exists($class, false);
    });
    AnnotationRegistry::registerFile(APPPATH.'libraries/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

    // Database connection information
    $connectionOptions = array(
        'driver'   => 'pdo_mysql',
        'user'     => $db['default']['username'],
        'password' => $db['default']['password'],
        'host'     => $db['default']['hostname'],
        'dbname'   => $db['default']['database']
    );

    // Create EntityManager
    $this->em = Doctrine\ORM\EntityManager::create($connectionOptions, $config);
  }

  /**
   * @return Doctrine\ORM\EntityManager
   * -----------------------------------------------------
   * On controller:
   *     use Entity\User;
   *     ...
   *     $em     = $this->doctrine->getEntityManager();
   *     $entity = $em->getRepository('Entity\User')->find(1);
   */
  public function getEntityManager()
  {
  	return $this->em;
  }

  /**
   * @return Symfony\Component\Validator\Validation
   * ----------------------------------------------------
   * On controller:
   *     <note : $user is entity object>
   *     
   *     $em        = $this->doctrine->getEntityManager();
   *     $violation = $this->doctrine->getValidator()->validate($user);
   *     if (null !== $violation) {
   *         $datas['errors'] = $violation;
   *     } else {
   *         $em->persist($user);
   *         $em->flush();
   *     }
   */
  public function getValidator() 
  {
    if (null === $this->validator) {
        $this->validator = Symfony\Component\Validator\Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();        
    }

    return $this->validator;
  }
}
