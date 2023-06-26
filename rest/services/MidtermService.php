<?php
require_once __DIR__ . "/../dao/MidtermDao.php";

class MidtermService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new MidtermDao();
    }

    public function investor($first_name, $last_name, $email, $company, $share_class_id, $share_class_category_id, $diluted_shares)
    {
        return $this->dao->investor(
            $first_name,
            $last_name,
            $email,
            $company,
            $share_class_id,
            $share_class_category_id,
            $diluted_shares
        );
    }

    public function investor_email($email)
    {
        return $this->dao->investor_email($email);
    }

    public function investors($share_class_id)
    {
        return $this->dao->investors($share_class_id);
    }
}
?>