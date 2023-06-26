<?php
require_once "BaseDao.php";

class MidtermDao extends BaseDao
{

    // private $conn;

    public function __construct()
    {
        parent::__construct();
        // $this->conn = parent::$conn;
    }

    /** TODO
     * Implement DAO method used add new investor to investor table and cap-table
     */
    public function investor($first_name, $last_name, $email, $company, $share_class_id, $share_class_category_id, $diluted_shares)
    {
        return parent::investor(
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
        return parent::investor_email($email);
    }

    public function investors($share_class_id)
    {
        return parent::investors($share_class_id);
    }

}
?>