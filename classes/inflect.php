<?php
/**
 * User: Stanislav Vetlovskiy
 * Date: 17.02.13
 */

class Inflect
{
    /** @var Save */
    private $save;
    /** @var Request */
    private $request;

    public function __construct(Save $save, Request $request)
    {
        $this->save = $save;
        $this->request = $request;
    }

    public function processFile($file)
    {
        $wordsList = fopen($file, 'r');
        if (!flock($wordsList, LOCK_EX | LOCK_NB)) {
            echo "Файл $file заблокирован!\n";
            fclose($wordsList);

            return false;
        }
        echo "Обработка файла $file\n";
        $i = 0;
        while ($row = fgetcsv($wordsList, 1000, ';')) {
            $result = $this->processRow($row);
            if (!$result) {
                continue;
            }
            $i++;
            sleep(1);
        }
        fclose($wordsList);
        echo "Обработка файла $file закончена\n";
        echo "Добавленно в базу $i значений\n";

        return $i;
    }

    public function processRow(array $row)
    {
        // check of existing current word in db
        $exist = $this->save->getWordsByMetaId($row[0]);
        if (!empty($exist)) {
            echo "Слово '{$row[1]}' с meta_id {$row[0]} есть в базе!\n";

            return false;
        }
        // check for inflects to this word
        if ($inflectId = $this->save->getInflectIdByWord($row[1])) {
            $this->save->addWord($row[0], $row[1], $inflectId);
            echo "Слово {$row[0]} дубляж!\n";

            return false;
        }
        $inflects = $this->request->get($row[1]);
        $result = $this->save->proceed($row[0], $row[1], $inflects);
        if ($result) {
            echo $row[1] . " (" . $row[0] . '): ' . count(
                array_filter($inflects, function ($a) { return !empty($a); })
            ) . "\n";
        } else {
            echo $row[0] . ': Ошибка!' . "\n";
            print_r($inflects);
            print_r($result);
            exit;
        }

        return true;
    }
}
