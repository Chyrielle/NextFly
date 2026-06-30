# Project Herdian
# Kelompok-5 Sistem Booking Tiket Perjalanan

Project mata kuliah **Pengembangan Sistem Backend (SI253314)** — Kelompok 5

> Sistem untuk booking tiket travel seperti bus, kereta, dan juga pesawat beserta booking hotel

---

## Anggota Kelompok

| No | Nama | NIM | Tugas |
|----|------|-----|-------|
| 1 | Made Arya Dharma Putra Mahothama | 250030020 | Phpmailer Google Regis |
| 2 | I Gusti Agung Bramantha Prana Citra J. | 250030061 | All Validation Value Role |
| 3 | I Putu Yoga Pratama | 250030062 | Transaksi PHPmailer Gmail |
| 4 | Gede Agung Bagus Aryadinatha | 250030094 | Alur Pembelian PHP |
| 5 | I Dewa Agung Ayu Lidya Aristawati | 250030485 | Desain via CSS & HTML |

---

## Struktur Folder

```
travel-ticket-booking/
│
├── api/                    ←Bram
│   ├── login.php
│   ├── logout.php
│   └── users.php
│
├── classes/                ←Bram & Yoga
│   └──infouser.php
│   
├── config/                 ←Bram   
│   ├── database.php
│   ├── session.php
│   ├── config.php
│   └── roles.php
│
├── controllers/             ←Bram, Yoga & Ajus
│   ├── AuthController.php          
│   ├── BookingController.php        
│   ├── PaymentController.php        
│   ├── UserController.php
│   └── AdminController.php          
│
├── models/                  ←Ajus & Yoga
│   ├── User.php
│   ├── Ticket.php
│   ├── Booking.php
│   ├── Payment.php
│   └── Passenger.php
│
├── middleware/              ←Bram
│   ├── auth.php
│   ├── admin.php
│   ├── user.php
│   └── validation.php
│
├── mail/                     ←Arya & Yoga
│   ├── PHPMailer/
│   ├── MailConfig.php
│   ├── RegisterMail.php
│   ├── VerificationMail.php
│   ├── TransactionMail.php
│   └── InvoiceMail.php
│
├── Validation Role/         ←Bram & Lidya
│   ├── user/
│   │   ├── dashboard.html
│   │   ├── history.php
│   │   └── profile.php
│   │
│   └── admin/                       
│   │   ├── dashboard.html 
│   │   ├── users.php
│   │   ├── bookings.php
│   │   ├── payments.php
│   │   ├── schedules.php
│   │   └── reports.php
├── booking/                 ←Ajus
│   ├── Booking Page.php
│   ├── search.php
│   ├── flight.php
│   ├── train.php
│   ├── bus.php
│   ├── passenger.php
│   └── checkout.php
│   │
│   ├── payment/             ←Yoga
│   │   ├── Payment Page.php ←Lidya  
│   │   ├── payment.php
│   │   ├── success.php
│   │   ├── failed.php
│   │   └── invoice.php
│
├── database/             ←Yoga & Bram
│   ├── database.sql
│   ├── profile/
│   └── payment-proof/
│
├── vendor/
│
├── login.html           ←Arya
├── register.html        ←Arya
├── register.php         ←Arya
├── composer.json        ←Arya
├── composer.lock        ←Arya
├── Homepage.html        ←Lidya
├── composer.json`       ←Arya
├── README.md
└── .gitignore
