<?php

interface UserInterface {
    public function findByEmail($email);
    public function FINDByUsername($username);
    public function findByFirstName($firstname);
    public function FindByLastName($lastname);
    public function findByName($name);
    public function findById($id);

    public function insert($user);
    public function update($user);
}

interface UserInterfaceOK {
    public function findByEmail($email);
    public function findByUsername($username);

    public function insert($user);
    public function update($user);
}
?>