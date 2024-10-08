<?php
class PenggunaController extends GLOBAL_Controller {

    public function __construct() {
        parent::__construct();
        $model = array('PenggunaModel', 'HistoryModel'); // Load HistoryModel
        $this->load->model($model);
        // Pastikan admin sudah login dan memiliki hak akses yang benar
        if (!parent::hasLogin()) {
            $this->session->set_flashdata('alert', 'belum_login');
            redirect(base_url('login'));
        }
        $level = $this->session->userdata('level');
        if ($level == 'user') {
            redirect(base_url());
        }
        $this->HistoryModel->deleteOldMessages();
    }

    public function index() {
        $data['title'] = 'Daftar Pengguna ';
        $data['Pengguna'] = parent::model('PenggunaModel')->get_users();

        parent::template('pengguna/index', $data);
    }

    // Fungsi untuk menambahkan pesan ke history
    private function addMessage($text, $summary, $icon)
    {
        $data = [
            'message_text' => $text,
            'message_summary' => $summary,
            'message_icon' => $icon,
            'message_date_time' => date('Y-m-d H:i:s'),
            'role' => $this->session->userdata('level')
        ];
        $this->HistoryModel->addMessage($data);
    }

    public function ubah($id) 
    {
        if (isset($_POST['ubah'])) {
            $data = array(
                'nama_lengkap' => parent::post('nama_lengkap'),
                'username' => parent::post('username'),
                'email' => parent::post('email'),
                'password' => parent::post('password'),
                'satker' => parent::post('satker'),
                'limit_total' => parent::post('limit_total'),
                'pengguna_hak_akses' => parent::post('pengguna_hak_akses'),
            );
            $simpan = parent::model('PenggunaModel')->ubah($id, $data);

            // Ambil nama pengguna untuk pesan
            $nama_pengguna = parent::post('username');

            if ($simpan > 0) {
                $this->addMessage('Pengguna dengan nama ' . $nama_pengguna . ' telah diubah', 'Pengguna diubah', 'update');
                parent::alert('alert', 'sukses_ubah');
                redirect('pengguna');
            } else {
                parent::alert('alert', 'gagal_ubah');
                redirect('pengguna');
            }
        } else {
            $data['title'] = 'Ubah Pengguna';
            $query = array('pengguna_id' => $id);
            $data['Pengguna'] = parent::model('PenggunaModel')->Lihat_Pengguna($query);
            parent::template('pengguna/ubah', $data);
        }
    }

    public function hapus($id)
    {
        $query = array('pengguna_id' => $id);
        $pengguna = parent::model('PenggunaModel')->Lihat_Pengguna($query); // Ambil nama pengguna
        $hapus = parent::model('PenggunaModel')->hapus($query);
        if ($hapus > 0) {
            $this->addMessage('Pengguna dengan nama ' . $pengguna->username . ' telah dihapus', 'Pengguna dihapus', 'delete');
            parent::alert('alert', 'sukses_hapus');
            redirect('pengguna');
        } else {
            parent::alert('alert', 'gagal_hapus');
            redirect('pengguna');
        }
    }

    public function tambah()
    {
        if (isset($_POST['tambah'])) {
            $data = array(
                'nama_lengkap' => parent::post('nama_lengkap'),
                'username' => parent::post('username'),
                'email' => parent::post('email'),
                'satker' => parent::post('satker'),
                'limit_total' => 1500000,
                'password' => parent::post('password'),
                'pengguna_hak_akses' => parent::post('level')
            );

            $simpan = parent::model('PenggunaModel')->tambah($data);

            // Ambil nama pengguna untuk pesan
            $nama_pengguna = $data['nama_lengkap'];

            if ($simpan > 0) {
                $this->addMessage('Pengguna dengan nama ' . $nama_pengguna . ' telah ditambahkan', 'Pengguna ditambahkan', 'add_circle_outline');
                parent::alert('alert', 'sukses_tambah');
                redirect('pengguna');
            } else {
                parent::alert('alert', 'gagal_tambah');
                redirect('pengguna');
            }
        } else {
            $data['title'] = 'Tambah Pengguna Koperasi Baru';
            parent::template('pengguna/tambah', $data);
        }
    }

    public function profile($userID)
    {
        // Mengecek apakah user sudah login
        if (!$this->hasLogin()) {
            redirect('login'); // Redirect ke halaman login jika belum login
        }
        
        // Mengambil data profil user berdasarkan session user_id
        $data['user'] = $this->ProfileModel->get_user_by_id($userID);

        // Memuat template dan mengirim data ke view
        $this->template('profile/index', $data);
    }

    public function limit()
    {
        $data['title'] = 'Limit Pengguna Koperasi Baru';
        $data['Pengguna'] = parent::model('PenggunaModel')->get_users();

        parent::template('limit/index', $data);
    }

    public function save_limit_total($id) {
        if (isset($_POST['save_limit_total'])) {
            $total_limit = parent::post('limit_total');
            parent::model('PenggunaModel')->save_total_limit($id, $total_limit);

            $this->addMessage('Total limit untuk pengguna dengan ID ' . $id . ' telah diperbarui', 'Limit diperbarui', 'update');
            parent::alert('alert', 'sukses_ubah');
            redirect('limit');
        }
    }

    public function reset_limit($id) {
        parent::model('PenggunaModel')->reset_limit($id);
        $this->addMessage('Limit untuk pengguna dengan ID ' . $id . ' telah direset', 'Limit direset', 'update');
        parent::alert('alert', 'sukses_reset');
        redirect('limit');
    }

    public function reduce_limit($id) {
        if (isset($_POST['amount'])) { 
            $amount = parent::post('amount');
            
            // Validasi jumlah yang dimasukkan
            if ($amount <= 0) {
                parent::alert('alert', 'error-insert');
                redirect('limit');
            }

            // Ambil limit pengguna saat ini
            $current_limit = parent::model('PenggunaModel')->get_user_limit($id);

            // Cek apakah limit cukup untuk dikurangi
            if ($current_limit < $amount) {
                parent::alert('alert', 'error-insert');
                redirect('limit');
            }

            // Kurangi limit pengguna
            parent::model('PenggunaModel')->reduce_user_limit($id, $amount);

            // Tambahkan pesan ke history
            $this->addMessage('Limit untuk pengguna dengan ID ' . $id . ' telah dikurangi sebesar ' . $amount, 'Limit dikurangi', 'update');
            parent::alert('alert', 'success-insert');
            redirect('limit');
        } else {
            redirect('limit');
        }
    }
    

}