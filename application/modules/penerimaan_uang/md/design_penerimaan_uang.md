# System Design Document: Modul Penerimaan Uang (Penerimaan Piutang)

## 1. Pendahuluan
Dokumen ini menguraikan arsitektur sistem, desain antarmuka (UI/UX), spesifikasi basis data, dan diagram urutan (*Sequence Diagram*) untuk modul **Penerimaan Uang**. Dokumen ini difokuskan pada "bagaimana" modul ini dibangun secara teknis, melengkapi dokumen PRD (*Product Requirements Document*) yang berfokus pada alur bisnis.

---

## 2. Desain Antarmuka Pengguna (UI/UX)
Sistem menggunakan *stack* standar berupa Bootstrap, jQuery, dan DataTables.

### 2.1. Halaman Utama (Dashboard Alokasi)
Halaman ini (`views/index.php`) berfungsi sebagai pusat pemantauan dana masuk yang belum / sudah di-*follow-up*.
- **Filter Bar:** Terdapat *dropdown* filter "Bank" untuk menyaring mutasi berdasarkan bank penerima.
- **DataTables Server-Side:** Menampilkan daftar dana masuk secara asinkron (AJAX ke `get_alokasi_penerimaan()`).
- **Komponen UI Kolom Status:**
  - Dana Belum Diproses: Menggunakan komponen `<span class="badge bg-yellow">Draft</span>`.
  - Dana Sudah Diproses: Menggunakan komponen `<span class="badge bg-green">Used</span>`.
- **Komponen UI Kolom Action:**
  - Jika Draft: Tombol biru (`btn-primary`) dengan ikon `<i class="fa fa-plus"></i>` untuk melakukan Alokasi.
  - Jika Used: Tombol detail (`btn-info`) dengan ikon mata `<i class="fa fa-eye"></i>`, dan tombol *Rollback* (`btn-danger`) dengan ikon *undo* `<i class="fa fa-undo"></i>` jika jurnal belum di-*posting*.

### 2.2. Form Alokasi & Pelunasan Invoice
Halaman form (`views/add.php` dan `views/process.php`) mengandalkan tabel interaktif untuk *data entry*.
- **Pemilihan Customer:** Dropdown *Select2* (atau *combo box*) untuk mencari customer. Saat diubah (`onchange`), sistem memicu AJAX `get_inv_by_cust()`.
- **Daftar Invoice:** Ditampilkan dalam format tabel. Invoice *Non-Konsultasi* diberi label khusus `<span class="label label-warning">Non Kons</span>`.
- **Input Kalkulasi Dinamis:**
  Terdapat *text input* berformat angka (menggunakan library `autonum` / *auto-numeric*). Setiap input (`penerimaan`, `biaya_admin`) memicu *event* `onkeyup="hitungAll()"` di JavaScript untuk memvalidasi secara *real-time* saldo akhir dan kontrol selisih (Zero Balance Check).
- **Validasi Submit:** Tombol "Save" akan di-*disable* atau memunculkan peringatan (via *SweetAlert*) apabila field **Kontrol (Selisih)** tidak bernilai `0`.

---

## 3. Arsitektur Basis Data (Entity Relationship)

Berikut adalah desain *Schema* dan Relasi Data yang menggerakkan modul ini.

```mermaid
erDiagram
    TR_ALOKASI_SPLIT ||--o{ TR_ALOKASI_DETAIL : "references (id_alokasi_detail)"
    TR_PENERIMAAN_PIUTANG ||--|| TR_ALOKASI_SPLIT : "references (id_alokasi)"
    TR_PENERIMAAN_PIUTANG ||--o{ TR_PENERIMAAN_PIUTANG_DETAIL : "has many"
    TR_PENERIMAAN_PIUTANG_DETAIL }|--|| TR_INVOICING : "pays (id_inv)"
    TR_PENERIMAAN_PIUTANG ||--o{ TR_JURNAL : "generates (no_transaksi = no_surat)"

    TR_PENERIMAAN_PIUTANG {
        string no_surat PK
        int id_alokasi FK
        string id_customer
        string nm_customer
        decimal nominal_penerimaan_bank
    }

    TR_PENERIMAAN_PIUTANG_DETAIL {
        int id PK
        string id_header FK "no_surat"
        int id_inv FK
        decimal penerimaan
        decimal biaya_admin
        decimal sisa_piutang
    }

    TR_INVOICING {
        int id PK
        string no_invoice
        decimal saldo_piutang "Berkurang saat alokasi"
    }

    TR_JURNAL {
        int id PK
        string no_jurnal
        string no_transaksi FK "no_surat"
        string coa
        decimal debit
        decimal kredit
        int sts "0 = Draft, 1 = Posted"
    }
```

---

## 4. Sequence Diagram: Proses Simpan Alokasi (Save)

Diagram ini mengilustrasikan alur sistem saat User Finance melakukan *Submit* form alokasi penerimaan piutang.

```mermaid
sequenceDiagram
    participant UI as User Interface (Browser)
    participant C as Controller (Penerimaan_uang)
    participant M as Model (Penerimaan_uang_model)
    participant DB as Database (Finance/Acc)

    UI->>C: POST /save_penerimaan_piutang (Form Data)
    C->>M: generate_id() (Nomor Surat)
    M-->>C: no_surat (KRG/RKN...)
    C->>M: resolve_alokasi(id_alokasi)
    M-->>C: split & detail data (Bank Info)
    
    C->>DB: trans_begin()
    
    C->>DB: INSERT INTO tr_penerimaan_piutang (Header)
    
    loop Setiap Invoice Terpilih
        C->>DB: SELECT data invoice & penawaran
        C->>DB: INSERT INTO tr_penerimaan_piutang_detail
        C->>DB: UPDATE tr_invoicing (kurangi saldo_piutang)
        
        C->>M: generate_id_invoice_jurnal()
        M-->>C: no_jurnal (AJV)
        C->>DB: INSERT INTO tr_jurnal (sts = 0)
    end
    
    C->>DB: UPDATE tr_alokasi_detail / split (set nilai_terpakai)
    
    alt Jika ada error query
        C->>DB: trans_rollback()
        C-->>UI: JSON {status: 0, msg: "Please try again later !"}
    else Jika sukses semua
        C->>DB: trans_commit()
        C-->>UI: JSON {status: 1, msg: "Save penerimaan berhasil !"}
    end
```

---

## 5. Sequence Diagram: Fitur Rollback

Alur pembatalan transaksi penerimaan uang. Mengutamakan integritas jurnal akuntansi.

```mermaid
sequenceDiagram
    participant UI as User Interface
    participant C as Controller (rollback_penerimaan)
    participant DB as Database (Finance)

    UI->>C: POST /rollback_penerimaan (id_penerimaan)
    
    C->>DB: SELECT * FROM tr_penerimaan_piutang
    DB-->>C: Data Header
    
    C->>DB: SELECT COUNT(*) FROM tr_jurnal WHERE sts=1
    DB-->>C: check_posted
    
    alt check_posted > 0
        C-->>UI: JSON Error "Jurnal sudah di-posting!"
    else check_posted == 0
        C->>DB: trans_begin()
        
        C->>DB: SELECT * FROM tr_penerimaan_piutang_detail
        loop Setiap Detail
            C->>DB: UPDATE tr_invoicing (tambah kembali saldo_piutang)
        end
        
        C->>DB: UPDATE tr_alokasi_detail (nilai_terpakai = NULL, sts = 0)
        C->>DB: DELETE FROM tr_alokasi_split
        C->>DB: INSERT INTO log_alokasi_history (Audit Trail)
        
        C->>DB: trans_commit()
        C-->>UI: JSON Success "Rollback berhasil"
    end
```

---
*Dokumen desain ini menggambarkan kondisi implementasi kodingan saat ini. Semua perubahan struktur database atau flow validasi diwajibkan untuk disinkronisasi kembali ke dokumen ini.*
