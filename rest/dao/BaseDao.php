<?php

class BaseDao
{

  private $conn;

  /**
   * constructor of dao class
   */
  public function __construct()
  {
    try {

      $host = 'db-mysql-nyc1-13993-do-user-3246313-0.b.db.ondigitalocean.com';
      $user = 'doadmin';
      $pass = 'AVNS_z6PG_c6BSn-5dB0CG5S';
      $schema = 'final-midterm2-2023';
      $port = '25060';

      $options = array(
        PDO::MYSQL_ATTR_SSL_CA => 'https://drive.google.com/file/d/1zqyqk92mI4A4cAW43nhnCWxEveGSkY7k/view?usp=sharing',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,

      );

      $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$schema", $user, $pass, $options);

      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      echo "Connected successfully";
    } catch (PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }
  }

  public function investor(
    $first_name,
    $last_name,
    $email,
    $company,
    $share_class_id,
    $share_class_category_id,
    $diluted_shares
  ) {

    //Check email
    $query = "SELECT COUNT(*) as count FROM investors WHERE email = :email";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch();

    if ($result['count'] > 0) {
      return ['message' => 'Another user already uses this email adress'];
    }

    $query2 = "SELECT SUM(diluted_shares) AS total FROM cap_table WHERE share_class_id = :share_class_id";
    $stmt2 = $this->conn->prepare($query2);
    $stmt2->bindParam(':share_class_id', $share_class_id);
    $stmt2->execute();
    $result2 = $stmt2->fetch();


    $query3 = "SELECT authorized_assets FROM share_classes WHERE id = :id";
    $stmt3 = $this->conn->prepare($query3);
    $stmt3->bindParam(':id', $share_class_id);
    $stmt3->execute();
    $result3 = $stmt3->fetch();

    $total = $result2['total'];

    if ($diluted_shares + $total < $result3['authorized_assets']) {

      $query = "INSERT INTO investors (first_name, last_name, email, company) VALUES (:first_name, :last_name, :email, :company)";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':first_name', $first_name);
      $stmt->bindParam(':last_name', $last_name);
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':company', $company);
      $stmt->execute();

      $investor_id = $this->conn->lastInsertId();

      $query = "INSERT INTO cap_table (share_class_id, share_class_category_id, investor_id, diluted_shares) VALUES (:share_class_id, :share_class_category_id, :investor_id, :diluted_shares)";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':share_class_id', $share_class_id);
      $stmt->bindParam(':share_class_category_id', $share_class_category_id);
      $stmt->bindParam(':investor_id', $investor_id);
      $stmt->bindParam(':diluted_shares', $diluted_shares);
      $stmt->execute();

      return ['message' => 'Success, added new investor and a new entry to the cap table'];

    } else {
      return ['message' => 'Can not add that many diluted shares'];
    }
  }

  public function investor_email($email)
  {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['message' => 'Invalid email format'];
    }

    $query = "SELECT COUNT(*) as count FROM investors WHERE email = :email";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch();

    if ($result['count'] > 0) {
      $stmt = $this->conn->prepare("SELECT first_name, last_name FROM investors WHERE email = :email");
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // return $response;

      return ['message' => 'Investor ' . $response[0]['first_name'] . ' ' . $response[0]['last_name'] . ' uses this email adress'];
    }

    return ['message' => 'Investor with this email does not exists in database'];
  }


  public function investors($share_class_id)
  {
    $stmt = $this->conn->prepare("SELECT 
                                    sc.description, 
                                    sc.equity_main_currency, 
                                    sc.price,
                                    i.email, 
                                    i.first_name,
                                    i.last_name,
                                    i.company,
                                    SUM(ct.diluted_shares) as total_diluted_shares
                                  FROM cap_table AS ct
                                  JOIN investors AS i ON ct.investor_id=i.id
                                  JOIN share_classes AS sc ON ct.share_class_id=sc.id
                                  WHERE sc.id = :id
                                  GROUP BY i.id;");

    $stmt->bindParam(':id', $share_class_id);
    $stmt->execute();
    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $response;
  }
}
?>