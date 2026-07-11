# Project Herdian - Next Fly
# Kelompok 5 Sistem Booking Tiket Perjalanan dan Reservasi

Project Mata Kuliah **Pengembangan Sistem Backend (SI253314)**

> Sistem untuk booking tiket travel seperti bus, kereta, pesawat, serta booking hotel secara online

---

## Anggota Kelompok

| No | Nama | NIM | Tugas |
|----|------|-----|-------|
| 1 | Made Arya Dharma Putra Mahothama | 250030020 | Login & Registration |
| 2 | I Gusti Agung Bramantha Prana Citra J. | 250030061 | All Validation Value Role |
| 3 | I Putu Yoga Pratama | 250030062 | Transaction PHPMailer Gmail |
| 4 | Gede Agung Bagus Aryadinatha | 250030094 | Application Workflow |
| 5 | I Dewa Agung Ayu Lidya Aristawati | 250030485 | Desainer |

---

## Struktur Folder
```
Nextfly/
├── api/                      ← Arya
│   ├── login.php             
│   ├── logout.php            
│   └── users.php             
├── classes/                  ← Arya & Bram
│   └── infouser.php          
├── config/                   ← Arya
│   ├── database.php
│   └── web_db.sql          
├── role/
│   ├── user/                 ← Lidya, Ajuz, Yoga, & Bram
│   │   ├── dashboard.html    
│   │   ├── user.php          
│   │   ├── booking-page.html 
│   │   ├── booking.php       
│   │   ├── payment.html      
│   │   └── sendmail.php       
│   ├── costumer-service/     ← Lidya & Bram
│   │   ├── dashboard.html    
│   │   ├── CS.php            
│   │   ├── reports.php       
│   │   └── history.php       
│   └── admin/                ← Lidya & Bram
│   │   ├── dashboard.html    
│   │   └── admin.php         
├── vendor/
├── login.html                ← Arya
├── register.html             ← Arya
├── register.php              ← Arya
├── composer.json             ← Arya
├── composer.lock             ← Arya
├── homepage.html             ← Arya & Lidya
├── style.css                 ← Lidya
└── README.md
