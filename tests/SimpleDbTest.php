<?php

use voku\db\DB;

class SimpleMySQLiTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var DB
   */
  public $db;

  public $tableName = 'test_page';

  public function __construct()
  {
    $this->db = DB::getInstance('localhost', 'root', '', 'mysql_test');
  }

  function testBasics()
  {

    // insert
    $pageArray = array(
        'page_template' => 'tpl_new',
        'page_type'     => 'lall'
    );
    $tmpId = $this->db->insert($this->tableName, $pageArray);

    // check (select)
    $result = $this->db->select($this->tableName, "page_id = $tmpId");
    $tmpPage = $result->fetchObject();
    $this->assertEquals('tpl_new', $tmpPage->page_template);

    // update
    $pageArray = array(
        'page_template' => 'tpl_update'
    );
    $this->db->update($this->tableName, $pageArray, "page_id = $tmpId");

    // check (select)
    $result = $this->db->select($this->tableName, "page_id = $tmpId");
    $tmpPage = $result->fetchObject();
    $this->assertEquals('tpl_update', $tmpPage->page_template);
  }

  public function testQry()
  {
    $result = $this->db->qry(
        "UPDATE " . $this->db->escape($this->tableName) . "
      SET
        page_template = 'tpl_test'
      WHERE page_id = ?
    ", 1
    );
    $this->assertEquals(1, ($result));

    $result = $this->db->qry("SELECT * FROM " . $this->db->escape($this->tableName) . "
      WHERE page_id = 1
    ");
    $this->assertEquals('tpl_test', ($result[0]['page_template']));

  }

  public function testConnector()
  {
    $data = array(
        'page_template' => 'tpl_test_new'
    );
    $where = array(
        'page_id LIKE' => '1'
    );

    // will return the number of effected rows
    $resultUpdate = $this->db->update($this->tableName, $data, $where);
    $this->assertEquals(1, $resultUpdate);

    $data = array(
        'page_template' => 'tpl_test_new2',
        'page_type'     => 'öäü'
    );

    // will return the auto-increment value of the new row
    $resultInsert = $this->db->insert($this->tableName, $data);
    $this->assertGreaterThan(1, $resultInsert);

    $where = array(
        'page_type =' => 'öäü',
        'page_type NOT LIKE' => '%öäü123',
        'page_id =' => $resultInsert,
    );

    $resultSelect = $this->db->select($this->tableName, $where);
    $resultSelectArray = $resultSelect->fetchAllArray();
    $this->assertEquals('öäü', $resultSelectArray[0]['page_type']);
  }
}
