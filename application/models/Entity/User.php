<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
// You can use Symfony2 Validator !!
// Check : https://github.com/symfony/Validator for more infos
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity\User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Entity\UserRepository")
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="string", length=55, nullable=false)
     */
    private $username;

    /**
     * @ORM\Column(name="password", type="string", length=55, nullable=false)
     */
    private $password;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }
}