<?php

interface ISecureLoginInterface{
    public function is_login_hash_valid(string $page_name, string $hash_key) : string;
    public function is_password_valid(string $user_email, string $password) : array;
    public function is_email_verified(string $user_email) : array;
    public function is_identity_verified(string $user_id) : array;
    public function is_email_already_exists(string $user_email) : int;
}