<?php

/**
 * clase que genera la insercion y edicion  de Datapush en la base de datos
 */
class Administracion_Model_DbTable_Datapush extends Db_Table
{
  /**
   * [ nombre de la tabla actual]
   * @var string
   */
  protected $_name = 'data_push';

  /**
   * [ identificador de la tabla actual en la base de datos]
   * @var string
   */
  protected $_id = 'data_id';

  /**
   * insert recibe la informacion de un Datapush y la inserta en la base de datos
   * @param  array Array array con la informacion con la cual se va a realizar la insercion en la base de datos
   * @return integer      identificador del  registro que se inserto
   */
  public function insert($data)
  {
    $data_date = $data['data_date'];
    $data_response = $data['data_response'];
    $query = "INSERT INTO data_push( data_date, data_response) VALUES ( '$data_date', '$data_response')";
    $res = $this->_conn->query($query);
    return mysqli_insert_id($this->_conn->getConnection());
  }

  /**
   * update Recibe la informacion de un Datapush  y actualiza la informacion en la base de datos
   * @param  array Array Array con la informacion con la cual se va a realizar la actualizacion en la base de datos
   * @param  integer    identificador al cual se le va a realizar la actualizacion
   * @return void
   */
  public function update($data, $id)
  {

    $data_date = $data['data_date'];
    $data_response = $data['data_response'];
    $query = "UPDATE data_push SET  data_date = '$data_date', data_response = '$data_response' WHERE data_id = '" . $id . "'";
    $res = $this->_conn->query($query);
  }
  public function delete($filter)
  {
    $update = "DELETE FROM " . $this->_name . " WHERE " . $filter;
    $this->_conn->query($update);
  }
}
