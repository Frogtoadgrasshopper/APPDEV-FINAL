# APPDEV-FINAL
# CampusThread Hoodies

CampusThread Hoodies is a working plain-PHP university hoodie shop for Group 6. It includes a buyer storefront, size selection, persistent carts, checkout, manual payment choices, order recording, inventory management, and seller reports.

## Included Features

- Buyer registration with complete name, valid email, password confirmation, complete address, and contact numbers.
- Email confirmation token after registration.
- Buyer login, categorized store page, size selection, add to cart, cart update, checkout, manual payment selection, and order confirmation.
- Ten hoodie designs with photorealistic catalog imagery, four available sizes, pricing, descriptions, and live stock counts.
- Seller admin dashboard.
- Admin user add/modify page with buyer, admin, and super admin roles.
- Admin product add/modify page for hoodie stocks, quantities, prices, and active status.
- Reports page with remaining inventory and audit log report.
- Group name and logo on every page.
- Footer disclaimer on every page.
- MySQL database file for project submission.

## Catalog Images

The included product photographs are original AI-generated catalog assets created for this project. They are suitable for the working demo and do not copy another clothing brand. Before selling manufactured garments to real customers, replace them with photographs of the physical inventory so the delivered product exactly matches the listing.

## Before Public Launch

- Configure a hosted MySQL database and import `database/university_hoodies_mysql.sql`.
- Set `APP_URL`, database credentials, and a working transactional email service.
- Serve the application over HTTPS.
- Confirm physical stock for every listed size and replace generated images with actual product photos.
- Connect a verified payment provider if online card or e-wallet payments are required; the current flow supports cash on delivery, campus pickup payment, and bank-transfer reservation.
