<?php

namespace TeamTNT\Indexer;

use TeamTNT\Stemmer\PorterStemmer;
use TeamTNT\Support\Collection;
use PDO;

class TNTIndexer
{
    public $storagePath = "";

    protected $index = null;
    protected $dbh   = null;

    public function createIndex($indexName)
    {
        $this->index = new PDO('sqlite:' . $this->storagePath . $indexName);
        return $this;
    }

    public function source($config = [])
    {
        $this->type = $config['type'];
        $this->db   = $config['db'];
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->pass = $config['pass'];

        $this->dbh = new PDO($this->type.':host='.$this->host.';dbname='.$this->db, $this->user, $this->pass);
        $this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($query)
    {
        $this->query = $query;
    }


    public function loadConfiguration($config = [])
    {
        $this->storagePath = $config['storage_path'];
    }

    public function run()
    {
        $result = $this->dbh->query($this->query);

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->processRow(new Collection($row));
        }
    }

    public function processRow($row)
    {
        $stems = $row->map(function($column, $name) {
            return $this->stemText($column);
        });
        print_r($stems);
    }

    public function stemText($text)
    {
        $stemmer = new PorterStemmer();
        $words = preg_split("/[ ,;\n\r\t]+/", trim($text));

        $stems = [];
        foreach($words as $word) {
            $stems[] = $stemmer->Stem(strtolower($word));
        }
        return $stems;
    }
}