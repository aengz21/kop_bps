<?php

// Mendefinisikan kelas GLOBAL_Model yang merupakan turunan dari CI_Model
class GLOBAL_Model extends CI_Model {
    
    // Konstruktor kelas GLOBAL_Model
    public function __construct()
    {
        parent::__construct();
        $this->load->database();  // Memuat database
    }
    
    // Metode untuk mendapatkan data dalam bentuk array dari sebuah tabel
    public function get_array_of_table($table)
    {
        $query =  $this->db->get($table);
        return $query->result_array();
    }

    // Metode untuk mendapatkan data dalam bentuk objek dari sebuah tabel
    public function get_object_of_table($table)
    {
        $query = $this->db->get($table);
        return $query;
    }

    // Metode untuk mendapatkan data satu baris dalam bentuk array berdasarkan query tertentu dari sebuah tabel
    public function get_array_of_row($table, $query)
    {
        $sql = $this->db->get_where($table, $query);
        return $sql->row_array();
    }

    // Metode untuk mendapatkan data satu baris dalam bentuk objek berdasarkan query tertentu dari sebuah tabel
    public function get_object_of_row($table, $query)
    {
        $sql = $this->db->get_where($table, $query);
        return $sql;
    }

    // Metode untuk mengembalikan objek database agar dapat digunakan langsung
    public function db()
    {
        return $this->db;
    }

    // Metode untuk melakukan join tabel
    public function get_join_table($sourcetable, $jointable)
    {
        $this->db->select('*');
        $this->db->from($sourcetable['name']);
        for ($i = 0; $i < count($jointable['table']); $i++) {
            $this->db->join($jointable['table'][$i], $jointable['table'][$i] . '.' . $jointable['id'][$i] . ' = ' . $sourcetable['name'] . '.' . $sourcetable[0][$i]);
        }
        $query = $this->db->get();
        return $query;
    }

    // Metode untuk memasukkan data ke dalam tabel
    public function insert_data($table, $data)
    {
        $this->db->insert($table, $data);
    }

    // Metode untuk memasukkan data ke dalam tabel dan mengembalikan status
    public function insert_with_status($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->affected_rows();
    }

    // Metode untuk memperbarui data dalam tabel berdasarkan key dan value tertentu
    public function update_table($table, $key, $value, $data)
    {
        $this->db->where($key, $value);
        $this->db->update($table, $data);
    }

    // Metode untuk memperbarui data dalam tabel dan mengembalikan status berdasarkan key dan value tertentu
    public function update_table_with_status($table, $key, $value, $data)
    {
        $this->db->where($key, $value);
        $this->db->update($table, $data);
        return $this->db->affected_rows();
    }

    // Metode untuk menghapus data dalam tabel berdasarkan query tertentu
    public function delete_row($table, $query)
    {
        return $this->db->delete($table, $query);
    }

    // Metode untuk menghapus data dalam tabel dan mengembalikan status berdasarkan query tertentu
    public function delete_row_with_status($table, $query)
    {
        $this->db->delete($table, $query);
        return $this->db->affected_rows();
    }

    // Metode untuk menjalankan query SQL dan menghasilkan hasil query
    public function exec_query($query)
    {
        return $this->db->query($query);
    }

	public function is_used($table, $column, $value)
	{
		$this->db->where($column, $value);
		$query = $this->db->get($table);
		return $query->num_rows() > 0;
	}
}
?>
