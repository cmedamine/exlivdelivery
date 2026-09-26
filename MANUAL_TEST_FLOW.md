# EXLIV Delivery — Manual Test Flow

Use this as a first pass through the application. The goal is to follow one parcel from creation to delivery, then confirm that the system keeps the same information throughout.

## Before you start

1. Use a **test/staging** installation if one is available. These steps create and update real records.
2. Ask an administrator for accounts with these roles: **Client**, **Confirmation agent**, **Courier (Livreur)**, and **Moderator**. One moderator account alone can run most of the flow.
3. Keep these sample values nearby:

| Field | Sample value |
| --- | --- |
| Recipient | Test Customer |
| Phone | 0612345678 |
| City | A city already configured in the app |
| Address | 10 Test Street |
| Product | Test T-shirt |
| Quantity | 1 |
| Price | 100 |
| Note | Manual test — safe to delete |

4. Record the parcel/shipment code after creation. Use it to search for the same record on every page.
5. In every test below, write **Pass** only if the result matches the expected result. Otherwise write **Fail**, include a screenshot, the parcel code, the user role, and what happened.

## The simple end-to-end journey

### 1. Sign in

1. Open `login.php`.
2. Enter a valid account email and password, then select **Se connecter**.

Expected result: the user enters the application. A moderator opens **Statistiques**; other roles normally open **Commandes**.

Also try an incorrect password.

Expected result: sign-in is refused with the message that the email or password is incorrect; no account is opened.

### 2. Create a parcel — Client journey

Sign in as **Client** and open **Ajouter Colis**.

1. Select **Normal** parcel type.
2. Enter the sample recipient details above.
3. Choose an existing city and enter the price, address, product, and quantity.
4. Select **Ajouter le Colis**.
5. Note the code assigned to the parcel.

Expected result: a success message appears, the parcel is created once only, and it can be found in **Commandes** by its code. The stored recipient, city, address, product, quantity, price, and note match what was entered.

Validation checks:

- Leave each required field empty one at a time (recipient, phone, city, price, address, product). Expected: creation is blocked and the missing field is clearly indicated.
- Enter an invalid phone such as `123`. Expected: the page refuses it or shows a meaningful error.
- Click the submit button twice quickly. Expected: only one parcel is created.

### 3. Confirm the order — Confirmation agent journey

Sign in as **Confirmation agent** and open **Confirmation**.

1. Search for the parcel code.
2. Open the order, verify the customer data, then use **Modifier état**.
3. Choose the configured status that represents confirmed/ready for processing and save it. If the app uses another label, choose the equivalent configured state.
4. Search again by code.

Expected result: the updated status is visible and the history records the change. The order is still linked to the same client and parcel code.

Additional check: enter a status comment and, when available, a report date. Expected: both values persist after refreshing the page.

### 4. Assign a courier — Moderator journey

Sign in as **Moderator**, then open **Commandes** or **Affectation commandes**.

1. Search for the parcel code.
2. Assign an existing courier (**Livreur**) to the order and save.
3. Open **Commandes sans livreur** and search for the code.
4. Log in as that courier and open **Commandes**.

Expected result: the parcel is no longer listed as unassigned and is visible to the assigned courier only. Its recipient details and price have not changed.

### 5. Update delivery progress — Courier journey

While signed in as the assigned **Courier**, open **Commandes** and search for the code.

1. Open **Modifier état**.
2. Set the state to the configured “out for delivery” equivalent and save.
3. Refresh and confirm it was retained.
4. Change it to the configured delivered/successful state and save.

Expected result: each status update is visible after refresh and appears in the order history with the correct user/date. The final state is delivered.

Optional exception path: create a second test parcel and use a failed, postponed, or returned status. Add a comment; if choosing postponed, add a report date. Expected: the result and comment appear in history and the parcel does not incorrectly show as delivered.

### 6. Verify tracking and record history

As moderator or client, locate the completed parcel in **Commandes**.

1. Search using the parcel code.
2. Open **Historique du commande** (order history) if available in the actions menu.
3. Compare the history with your notes: created, confirmed, assigned, in delivery, delivered.

Expected result: all changes occurred in the right order, without missing or duplicate transitions. The final state agrees with the courier update.

### 7. Verify related business records

These checks depend on permissions and how your company uses the application.

1. Open **Bons** and create/view the relevant delivery or pickup document for the courier.
2. Open **Factures** and check the corresponding client/courier invoice only after the parcel has a final status.
3. Open **Statistiques** as moderator and check whether the delivered order is reflected in the appropriate totals.

Expected result: documents and totals include the parcel once, show the right client/courier, and use the parcel’s price/fees according to your configured rules.

## Quick feature checklist

Run these after the end-to-end journey. They are independent tests, so use fresh test data where a change is destructive.

| Area | What to do | Expected result |
| --- | --- | --- |
| Search and filters | Search by code, recipient, city, state, and date | Matching records appear; clearing filters restores the list |
| Edit order | Change a recipient address and save | New address remains after refresh and history reflects the change |
| Duplicate order | Use the duplicate action on a test parcel | A new parcel code is created; the original remains unchanged |
| Delete order | Delete only a disposable test parcel and confirm | Record is removed/hidden as designed and other orders remain intact |
| Import orders | Import a small file containing one valid row and one invalid row | Valid data is imported once; invalid data returns a clear error without corrupting existing data |
| Label/document printing | Print/preview an order label or delivery note | Correct parcel code, recipient, address, and courier are shown |
| Stock and shipments | Add a test shipment or stock item, then find it | Quantity and reference persist; invalid/zero quantity is rejected |
| Complaint | Create a reclamation linked to the parcel code and reply | Complaint, message, and reply remain visible to the permitted users |
| Permissions | Sign in as client, worker, courier, and moderator | Each sees only the menu items and data intended for their role |
| Logout/session | Log out, then use the browser Back button | Protected pages do not expose data without signing in again |

## What makes a good bug report

Use this template for each failure:

```text
Title: [Area] Short description
Account/role: Client / Confirmation agent / Courier / Moderator
Parcel code: XXXXX
Steps: 1. ... 2. ... 3. ...
Expected: ...
Actual: ...
Evidence: screenshot or screen recording
When: date and time
```

## First-session completion rule

Your first manual test is complete when one normal parcel has been created, confirmed, assigned, marked delivered, found in history, and checked in the relevant document/statistics page — with no unexpected error, duplicated parcel, or lost information.
