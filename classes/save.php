<?php
/**
 * User: Stanislav Vetlovskiy
 * Date: 16.02.2013
 */

class Save {
    /** @var PDO */
    private $dbh;

    function __construct(PDO $dbh){
        $this->dbh = $dbh;
    }

    /**
     * @param int $id
     * @param string $original
     * @param array $inflect
     *
     * @return bool
     */
    public function proceed($id, $original,array $inflect){
        try{
            $inflectId=$this->addInflects($inflect);
            return $this->addWord($id, $original, $inflectId);
        } catch (PDOException $e) {
            echo 'SQL ERROR: '."\n";
            echo 'Строка: '.$e->getLine()."\n";
            print_r($e->getMessage());
            return false;
        }
    }

    /**
     * @param int $id
     * @param string $original
     * @param int $inflectId
     *
     * @return bool
     */
    public function addWord($id, $original, $inflectId){
        $stmt = $this->dbh->prepare("
            INSERT INTO `words`
                (`word`,
                `meta_id`,
                `inflect_id`)
            VALUES (?, ?, ?);
        ");
        $success = $stmt->execute(array(
                $original,
                $id,
                $inflectId
            ));
        return $success;
    }

    /**
     * @param array $inflect
     *
     * @return int
     */
    private function addInflects(array $inflect){
        $stmt = $this->dbh->prepare("
            INSERT INTO `inflect`
                (`nominative`,
                `genitive`,
                `dative`,
                `accusative`,
                `instrumental`,
                `prepositional`)
            VALUES (?, ?, ?, ?, ?, ?);
        ");
        $stmt->execute($inflect);
        return $this->dbh->lastInsertId();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getWordsByMetaId($id){
        $stmt = $this->dbh->prepare("
            SELECT * FROM `words` WHERE `meta_id` = ? LIMIT 1
        ");
        $stmt->execute(array($id));

        return $stmt->fetch();
    }

    /**
     * @param string $word
     *
     * @return int
     */
    public function getInflectIdByWord($word){
        $stmt = $this->dbh->prepare("
            SELECT `inflect_id` FROM `words` WHERE `word` = ? LIMIT 1
        ");
        $stmt->execute(array($word));
        list($result) = $stmt->fetch();

        return $result;
    }
}
