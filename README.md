# personal-diary-php

# Жеке күнделік жүргізуге арналған веб-қосымша (Personal Diary)

## Жоба туралы
Бұл веб-қосымша пайдаланушыларға өздерінің күнделікті жазбаларын, ойлары мен әсерлерін қауіпсіз түрде сақтауға, өңдеуге және реттеуге мүмкіндік береді.

## Негізгі беттер тізімі
1. Басты бет (Landing page)
2. Тіркелу және кіру беті (Auth pages)
3. Жеке кабинет / Жазбалар тізімі (Dashboard / Diary entries)

## Дерекқор құрылымы (ER-диаграмма қысқаша)
- **users** кестесі (id, username, email, password)
- **entries** кестесі (id, user_id, title, content, created_at)
- **categories** кестесі (id, name)

## Іске асырылған функционал (PHP + MySQL/PDO)
- **Аутентификация:** `password_hash()` және `$_SESSION` арқылы қауіпсіз тіркелу, кіру және шығу (`auth.php`, `logout.php`).
- **Толық CRUD:** Күнделік жазбаларын қосу, оқу, өңдеу (`edit.php`) және өшіру (`diary.php`).
- **Дерекқор:** MySQL (`users`, `categories`, `entries`) кестелері, `LEFT JOIN` сұраныстары және SQL-инъекциядан қорғалған PDO Prepared Statements (`config/db.php`).
