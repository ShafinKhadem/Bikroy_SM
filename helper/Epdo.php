<?php

namespace BikroySM;

/**
 * Represent the Connection
 */
class Epdo {

    /**
     * PDO object
     * @var \PDO
     */
    private $pdo;

    /**
     * Initialize the object with a specified PDO object
     * @param \PDO $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * insert a new row into the users table
     * @param type $email
     * @param type $name
     * @param type $password
     * @return the id of the inserted row
     */
    public function insertUser($email, $name, $password) {
        // prepare statement for insert
        $sql = 'INSERT INTO users(email, name, password) VALUES(:email, :name, :password)';
        $stmt = $this->pdo->prepare($sql);

        // pass values to the statement
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':password', $password);

        // execute the insert statement
        $stmt->execute();

        // return generated id
        // return $this->pdo->lastInsertId('users_id_seq');
    }

   /**
     * Insert multiple users into the users table
     * @param array $users
     * @return a list of inserted ID
     */
    public function insertuserList($users) {
        $sql = 'INSERT INTO users(email, name, password) VALUES(:email, :name, :password)';
        $stmt = $this->pdo->prepare($sql);

        $idList = [];
        foreach ($users as $user) {
            $stmt->bindValue(':email', $user['email']);
            $stmt->bindValue(':name', $user['name']);
            $stmt->bindValue(':password', $user['password']);
            $stmt->execute();
            // $idList[] = $this->pdo->lastInsertId('users_id_seq');
        }
        return $idList;
    }



    /**
     * Update User based on the specified email
     * @param $email
     * @param $name
     * @param $password
     * @param $location
     * @param $sublocation
     * @return the number of row affected
     */
    public function updateUser($email, $name, $password, $location, $sublocation) {

        // sql statement to update a row in the User table
        $sql = 'UPDATE users '
                . 'SET name = :name, password = :password, location = :location, sublocation = :sublocation '
                . 'WHERE email = :email';

        $stmt = $this->pdo->prepare($sql);

        // bind values to the statement
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':sublocation', $sublocation);
        // update data in the database
        $stmt->execute();

        // return the number of row affected
        return $stmt->rowCount();
    }

    /**
     * Find user by email
     * @param int $email
     * @return a user object
     */
    public function findByPK($email) {
        // prepare SELECT statement
        $stmt = $this->pdo->prepare('SELECT email, name FROM users WHERE email = :email');
        // bind value to the :email parameter
        $stmt->bindValue(':email', $email);

        // execute the statement
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = [
                'email' => $row['email'],
                'name' => $row['name'],
            ];
        }
        return $users;
    }

    // zekono ekta user er password 8 letter er choto kore check korte paro ze keu i dhukbe na.
    public function transactionDemo() {
        try {
            $this->pdo->beginTransaction();
            $this->insertUser("shafinkhadem@gmail.com", "Nafiur Rahman Khadem", "shafinkhadem");
            $this->insertUser("mwashief@gmail.com", "Washief Hossain Mugdho", "washiefmugdho");
            $this->pdo->commit();
            echo "Transaction successful";
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            echo nl2br("Transaction unsuccessful. Exception thrown:\n");
            throw $e;
        }
    }

    /**
     * Call a simple stored procedure add(a, b)
     * @param int $a
     * @param int $b
     * @return int
     */
    public function add($a, $b) {
        $stmt = $this->pdo->prepare('SELECT * FROM add(:a,:b)');
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute([
            ':a' => $a,
            ':b' => $b
        ]);
        return $stmt->fetchColumn(0);
    }


    /**
     * Call a $sql (string) query
     * @return 2d array (table)
     */
    public function getQueryResults($sql) {
        $stmt = $this->pdo->query($sql);
        $returns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $returns;
    }

    /**
     * Call a $sql (string) query query which returns only single column per row
     * @return 1d array
     */
    public function getQueryResultsCol($sql) {
        $stmt = $this->pdo->query($sql);
        $returns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return $returns;
    }

    /**
     * Call a select * from $param (string) query
     * @return 2d array (table)
     */
    public function getFromWhere($from_where) {
        return $this->getQueryResults('SELECT * FROM '.$from_where);
    }

    /**
     * Call a select * from $param (string) query which returns only single column per row
     * @return 1d array
     */
    public function getFromWhereCol($from_where) {
        return $this->getQueryResultsCol('SELECT * FROM '.$from_where);
    }

    /**
     * Call a select * from $param query
     * @return first column of first row of result of that query (useful for functions that return value)
     */
    public function getFromWhereVal($from_where) {
        $stmt = $this->pdo->query('SELECT * FROM '.$from_where);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetchColumn(0);
    }
}