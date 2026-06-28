# Project Herdian
# Kerja Kerja Kerja
# Kelompok-5 Sistem Booking Tiket Perjalanan

Project mata kuliah **Pengembangan Sistem Backend (SI253314)** — Kelompok 5

> Sistem untuk booking tiket travel seperti bus, kereta, dan juga pesawat beserta booking hotel

---

## Anggota Kelompok

| No | Nama | NIM | Tugas |
|----|------|-----|-------|
| 1 | Made Arya Dharma Putra Mahothama | 250030020 | Phpmailer google regis |
| 2 | I Gusti Agung Bramantha Prana Citra J. | 250030061 | All validation value role |
| 3 | I Putu Yoga Pratama | 250030062 | Transaksi PHPmailer gmail |
| 4 | Gede Agung Bagus Aryadinatha | 250030094 | Alur pembelian PHP |
| 5 | I Dewa Agung Ayu Lidya Aristawati | 250030485 | Desain via CSS & HTML |

---

## Struktur Folder

```
travel-ticket-booking/
│
├── assets/                          ← Lidya
│   ├── css/
│   │   ├── style.css
│   │   ├── login.css
│   │   ├── register.css
│   │   ├── dashboard.css
│   │   ├── booking.css
│   │   ├── payment.css
│   │   ├── admin.css
│   │   └── responsive.css
│   │
│   ├── js/
│   │   ├── script.js
│   │   ├── validation.js
│   │   └── booking.js
│   │
│   ├── images/
│   └── icons/
│
├── config/                          ← Bram
│   ├── database.php
│   ├── session.php
│   ├── config.php
│   └── roles.php
│
├── controllers/
│   ├── AuthController.php           ← Arya
│   ├── BookingController.php        ← Ajus
│   ├── PaymentController.php        ← Yoga
│   ├── UserController.php
│   └── AdminController.php          ← Bram
│
├── models/
│   ├── User.php
│   ├── Ticket.php
│   ├── Booking.php
│   ├── Payment.php
│   └── Passenger.php
│
├── middleware/                      ← Bram
│   ├── auth.php
│   ├── admin.php
│   ├── user.php
│   └── validation.php
│
├── mail/                            ← Arya & Yoga
│   ├── PHPMailer/
│   ├── MailConfig.php
│   ├── RegisterMail.php
│   ├── VerificationMail.php
│   ├── TransactionMail.php
│   └── InvoiceMail.php
│
├── views/
│   ├── layouts/                     ← Lidya
│   │   ├── header.php
│   │   ├── navbar.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   │
│   ├── auth/                        ← Arya + Lidya
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── verify.php
│   │   └── forgot-password.php
│   │
│   ├── booking/                     ← Ajus + Lidya
│   │   ├── search.php
│   │   ├── flight.php
│   │   ├── train.php
│   │   ├── bus.php
│   │   ├── passenger.php
│   │   └── checkout.php
│   │
│   ├── payment/                     ← Yoga + Lidya
│   │   ├── payment.php
│   │   ├── success.php
│   │   ├── failed.php
│   │   └── invoice.php
│   │
│   ├── user/
│   │   ├── dashboard.php
│   │   ├── history.php
│   │   └── profile.php
│   │
│   └── admin/                       ← Bram
│       ├── dashboard.php
│       ├── users.php
│       ├── bookings.php
│       ├── payments.php
│       ├── schedules.php
│       └── reports.php
│
├── database/                        ← Bram
│   ├── database.sql
│   └── dummy.sql
│
├── uploads/
│   ├── profile/
│   └── payment-proof/
│
├── vendor/
│
├── index.php
├── composer.json
├── README.md
└── .gitignore
