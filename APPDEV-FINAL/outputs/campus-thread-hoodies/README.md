# CampusThread Hoodies

CampusThread Hoodies is a plain PHP university apparel website for Group 4. The selected single apparel product is university hoodies.

## Included Features

- Buyer registration with complete name, valid email, password confirmation, complete address, and contact numbers.
- Email confirmation token after registration.
- Buyer login, categorized store page, add to cart, cart update, checkout, payment page without payment API, and order confirmation.
- Seller admin dashboard.
- Admin user add/modify page with buyer, admin, and super admin roles.
- Admin product add/modify page for hoodie stocks, quantities, prices, and active status.
- Reports page with remaining inventory and audit log report.
- Group name and logo on every page.
- Footer disclaimer on every page.
- MySQL database file for project submission.

## Local Testing

1. Copy this folder into your local PHP server folder, such as XAMPP `htdocs`.
2. Open the project in the browser.
3. The default local demo uses SQLite and automatically creates `database/campus_thread_demo.sqlite`.
4. Log in with one of the accounts in `sample_accounts.txt`.

If your computer has PHP on the command line, you can also run:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

## MySQL Hosting Setup

For InfinityFree, AwardSpace, or a similar PHP/MySQL host:

1. Create a MySQL database from the hosting control panel.
2. Import `database/university_hoodies_mysql.sql`.
3. Edit `config/config.php`.
4. Change `DB_DRIVER` to `mysql`.
5. Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` to the hosting database values.
6. Set `APP_URL` to your hosted website URL.
7. Upload all project files.

Most free hosts support PHP `mail()`, but some disable it. The registration process attempts to send the confirmation email. In local demo mode, if mail is unavailable, the confirmation link is shown on the page so the system can still be tested.

## Group Details To Replace

Update these before final submission:

- Group member names in `config/config.php`.
- GitHub repository link in `submission_links.txt`.
- Hosted website link in `submission_links.txt`.
- Screenshot PDF using your teacher's posted template.

## Sample Product

The website sells one apparel product type only: university hoodies. The product categories are hoodie collections, not separate apparel products.
