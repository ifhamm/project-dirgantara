# MWS Refactor Notes

## Overview

Refactor ini memisahkan controller MWS yang sebelumnya sangat gemuk menjadi controller yang lebih kecil, service yang fokus, dan request validation yang terpisah:

- `MwsPartService` untuk operasi level worksheet / MWS utama.
- `MwsWorkflowService` untuk operasi level step, detail, mechanic, timer, approval, consumable, sub-step, dan duplicate.
- `MwsPartController` untuk part-level actions.
- `MwsWorkflowController` untuk step/workflow actions.
- `app/Http/Requests/Mws/*` untuk validasi setiap endpoint.

Tujuannya:

- controller lebih tipis,
- logic lebih mudah dicari,
- perubahan fitur lebih aman,
- kode lebih gampang diuji dan dirawat.

## File Responsibilities

### 1. `app/Http/Controllers/MwsPartController.php`

Controller ini hanya menangani action level MWS utama.

Isi utamanya:

- menerima request dari route,
- memanggil `MwsPartService`,
- mengembalikan response JSON, redirect, atau view.

Controller ini tidak lagi memegang sebagian besar business logic.

### 2. `app/Http/Controllers/MwsWorkflowController.php`

Controller ini memegang semua aksi operasional di dalam worksheet.

Isi utamanya:

- tambah / sisip / hapus step,
- detail step,
- mechanic assignment,
- timer,
- approval / finish,
- consumable,
- sub-step,
- attachment placeholder,
- duplicate.

Semua validation input untuk controller ini dipindah ke Form Request.

### 3. `app/Services/MwsPartService.php`

Service ini menangani operasi utama di level MWS part.

Fungsi yang ada di sini:

- `index()` - list MWS,
- `store()` - create MWS baru,
- `generateSteps()` - generate step dari template,
- `updateStep()` - update field inline step,
- `show()` - load data untuk halaman detail MWS,
- `update()` - update data MWS,
- `destroy()` - hapus MWS,
- `print()` - load data untuk halaman print,
- `sign()` - prepared / approved / verified sign,
- `cancelSign()` - batalkan tanda tangan,
- `updateDates()` - update tanggal start/finish.

Service ini juga punya helper internal:

- `syncTemplateSteps()` - mengisi step default berdasarkan job type template.

### 4. `app/Services/MwsWorkflowService.php`

Service ini menangani workflow operasional di dalam worksheet.

Fungsi yang ada di sini:

- step management: `storeStep()`, `insertStepAfter()`, `destroyStep()`, `bulkDeleteSteps()`
- detail per step: `storeDetail()`, `updateDetail()`, `destroyDetail()`
- mechanic: `signOn()`, `assignMechanic()`, `removeMechanic()`
- timer: `startTimer()`, `stopTimer()`
- approval: `approveStep()`, `unapproveStep()`, `finishStep()`, `unfinishStep()`, `finishFinalInspection()`
- consumable: `storeConsumable()`, `updateConsumable()`, `destroyConsumable()`
- caution/note: `updateStepCaution()`
- sub-step: `storeSubStep()`, `updateSubStep()`, `destroySubStep()`
- duplicate MWS: `duplicate()`

Service ini punya helper internal:

- `step()` - ambil step berdasarkan MWS part dan nomor step,
- `reorderSteps()` - rapikan urutan step setelah delete,
- `generateSubStepLabel()` - generate label a, b, c, dst.

### 5. `app/Http/Requests/Mws/*`

Folder ini berisi validasi per endpoint, contohnya:

- `StoreMwsPartRequest`
- `UpdateMwsPartRequest`
- `UpdateMwsStepRequest`
- `AssignMechanicRequest`
- `StoreConsumableRequest`
- `UpdateConsumableRequest`
- `MwsSubStepRequest`
- `UpdateStepCautionRequest`
- `UpdateMwsDatesRequest`

Pola ini menjaga controller tetap tipis dan reusable.

### 6. `routes/web.php`

Route tetap memakai endpoint yang sama, jadi UI tidak perlu berubah.

Perannya hanya memetakan URL ke controller method.

Contoh grup route yang penting:

- MWS CRUD,
- step management,
- detail,
- sub-step,
- mechanic,
- timer,
- approval,
- consumable,
- attachment,
- sign,
- print.

### 7. `resources/views/mws/show.blade.php`

View ini adalah halaman utama operasional MWS.

Perannya:

- menampilkan data MWS,
- menampilkan step dan status,
- memanggil function JavaScript untuk action seperti add step, approve, timer, upload attachment, dsb.

View ini tidak berisi business logic backend, hanya interaksi UI.

### 8. `resources/views/mws/print.blade.php`

View ini khusus untuk cetak MWS.

Isi output yang dicetak:

- informasi umum MWS,
- tabel step,
- plan man,
- plan hours,
- actual man,
- actual hours,
- tech,
- insp,
- status.

File ini otomatis memanggil `window.print()` saat halaman dibuka.

## Workflow Request

### A. Alur halaman detail MWS

1. User membuka route `mws.show`.
2. Route memanggil `MwsPartController@show`.
3. Controller meneruskan ke `MwsPartService@show`.
4. Service mengambil data:
   - `customer`,
   - `steps.subSteps`,
   - `consumables`.
5. Data dikirim ke `resources/views/mws/show.blade.php`.

### B. Alur create MWS

1. User submit form create.
2. Route memanggil `MwsPartController@store`.
3. `StoreMwsPartRequest` melakukan validasi input.
4. Controller meneruskan data tervalidasi ke `MwsPartService@store`.
5. Service:
   - generate `part_id`,
   - cari customer,
   - set status awal,
   - generate `iwo_no`,
   - create `mws_parts`,
   - generate template step,
   - simpan step ke `mws_steps`.
6. User diarahkan ke halaman show MWS.

### C. Alur step management

Semua aksi step seperti add, insert, delete, bulk delete, detail, sub-step, mechanic, timer, approval, dan finish:

1. UI memanggil endpoint route terkait.
2. Route memanggil controller.
3. Form Request memvalidasi payload jika endpoint butuh input.
4. Controller meneruskan ke `MwsWorkflowService`.
5. Service melakukan operasi ke tabel step terkait.
6. Response dikirim kembali sebagai JSON.

### D. Alur print MWS

1. User klik Print.
2. Route `mws.print` memanggil `MwsPartController@print`.
3. Controller meneruskan ke `MwsPartService@print`.
4. Service load relasi yang dibutuhkan.
5. View `resources/views/mws/print.blade.php` dirender.
6. Halaman langsung menjalankan `window.print()`.

### E. Legacy controller

- `app/Http/Controllers/MwsStepController.php` masih ada sebagai controller lama, tetapi tidak dipakai oleh route aktif.
- Ini sengaja dibiarkan agar tidak mengganggu behavior lama di luar alur utama yang sekarang dipakai UI.

## Tabel / Relasi Yang Dipakai

### Tabel utama

- `mws_parts`
- `mws_steps`
- `mws_sub_steps`
- `mws_consumables`
- `customers`
- `users`

### Relasi yang dipakai

- `MwsPart -> customer`
- `MwsPart -> steps`
- `MwsPart -> consumables`
- `MwsStep -> subSteps`
- `MwsStep -> part`
- `MwsStep -> mechanics` via field `man`

## Catatan Implementasi

- Nama model sub-step yang benar di project ini adalah `MwsSubStep`.
- Controller sekarang dibuat tipis, sedangkan logic utama pindah ke service dan validasi pindah ke Form Request.
- Pola ini memudahkan pengembangan fitur baru tanpa membuat controller makin besar.

## Next Improvement Suggestion

Kalau ingin lebih rapi lagi, service ini masih bisa dipecah menjadi:

- `MwsPartReadService`
- `MwsPartCommandService`
- `MwsWorkflowService`

Supaya logic baca data dan logic ubah data benar-benar terpisah.