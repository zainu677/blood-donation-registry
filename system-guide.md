# LifeLink Blood Donation Registry — Full Walkthrough Guide

This walks through every feature in the system, in the order that makes sense to test it. Follow it top to bottom once before presenting, and you'll have personally clicked through everything.

---

## 0. One-time setup

1. Unzip the project into `htdocs` (e.g. `C:\xampp\htdocs\blood-donation-registry`).
2. Drop your 5 image files (`sindano.jpg`, `tolewa.jpg`, `donation.jpg`, `register.jpg`, `tunza.jpg`) into that same folder, next to `homepage.css` and `style.css`.
3. Start **Apache** and **MySQL** in the XAMPP control panel — both must show green.
4. Go to `http://localhost/phpmyadmin`, click **Import**, choose `database/schema.sql`, click **Go**. This creates the `lifelink_db` database and every table, including seeded (empty) blood stock rows for all 8 blood types.
5. Visit `http://localhost/blood-donation-registry/create-admin.php` once in your browser. It creates:
   - Username: `admin`
   - Password: `admin123`
6. **Delete `create-admin.php`** right after — leaving it live means anyone could create another admin.

You're ready. Everything below assumes the site is at `http://localhost/blood-donation-registry/`.

---

## 1. Public pages (no login needed)

- Open **`homepage.html`**. Check the nav links scroll to sections, and the mobile menu button (☰) toggles the nav on a narrow window.
- Scroll to **Contact Us**, fill in all four fields, and click **Send Message**. The page should reload scrolled to that section with a green "Thank you for your support!" banner above the form. Try submitting it empty too — you should get a red "please fill in every field" banner instead.
- Click **Register** → goes to `register.html`. Notice the role dropdown only offers **Donor** and **Recipient** now — Hospital and Blood Bank accounts are created by admin only (see Section 3).
- Click **Log In** → goes to `LOGIN.html`.
- On `LOGIN.html`, click **Forgot password?** → goes to `forgot-password.html`. Submit any email — you'll land on a styled confirmation page either way, so the flow never reveals whether that email has an account.

---

## 2. Create your test accounts

Register **two** accounts publicly, then have admin create the other two:

**Self-register at `register.html`:**

| Field | Donor account | Recipient account |
|---|---|---|
| Age | e.g. 25 (must be 18–65) | any |
| Blood Group | pick one, e.g. `O+` | leave as "Not applicable" |
| Location | e.g. `Dodoma` | e.g. `Dodoma` |

Try registering with an age like `10` or `70` for the donor role first — you should see a live warning under the field, and if you ignore it and submit anyway, a red banner appears above the form instead of a blank error page.

After registering, you're sent to the login page with a green "Registration successful, awaiting approval" banner. **Don't log in yet** — the donor needs admin approval first.

**Admin creates these two** (do this in Section 3, step 3 below):
- One **Hospital Staff** account, same city as your donor
- One **Blood Bank Staff** account

---

## 3. Admin walkthrough

1. Log in at `LOGIN.html` with `admin` / `admin123`.
2. Check the dashboard's stat cards (Total Accounts, Pending Approval, Registered Donors, Donations Logged).
3. In the sidebar, click **Create Staff Account**. Create your Hospital Staff account and your Blood Bank Staff account here — both are approved instantly since you're vetting them personally. Note the password field is plain text on purpose, since you're handing it to that staff member directly.
4. Back on the dashboard table, find your donor account (status = **Pending**) and click **Approve**.
5. Click **Edit** on any account, change a field, **Save Changes**, confirm it updated.
6. Try **Delete** on a throwaway account to see the confirm-before-delete prompt, then cancel so you don't lose a real demo account.

Notice **Log a Donation** is no longer in admin's sidebar — that now belongs to hospital staff only (Section 5).

---

## 4. Donor walkthrough

1. Log out, log in with your donor account.
2. **Dashboard** — check the hero banner and four stat cards. Total Donations will show `0` until a hospital logs one for you (Section 5).
3. **Profile** — update phone/location, toggle Availability, Save.
4. **Book Appointment** — book one, see it listed with a status badge, then **Cancel** it and confirm the JS confirm-dialog appears first.
5. **Donation History**, **Campaigns**, **Notifications** — come back to these after Section 5, once a hospital has logged a donation and posted a request matching your blood type.

---

## 5. Hospital walkthrough

1. Log out, log in with the hospital account admin created for you. Since admin approved it at creation, phone numbers are visible in search immediately — no separate verification wait.
2. **Search Donors** — search your donor's blood type and location, confirm they appear with a visible phone number.
3. **Post Urgent Request** — same blood type and location as your donor. Submit — matching donors should appear immediately below.
4. **Log a Donation** — this is now hospital-only. Select your donor, pick a date, type a hospital name, save. Check it shows up under "Recent Donations" on the same page.
5. Go back to the **donor** account: Dashboard's Total Donations should now show `1`, Donation History should show the row, and Campaigns should show the request you posted in step 3.

---

## 6. Recipient walkthrough

1. Log out, log in with your recipient account.
2. **Find Donors** — same search as hospital, but confirm the phone number is **always hidden** here, even though the account works fine otherwise.
3. **My Requests** — post one, see it under "My Request History" with a status badge.
4. **Give Feedback** — click the stars to rate, write a message, submit, confirm it appears under "My Past Feedback" with the right number of filled stars.

---

## 7. Blood Bank Staff walkthrough

1. Log out, log in with the blood bank account admin created for you.
2. **Dashboard** — check the stat cards (Total Units in Stock starts at 0 for every type) and the small per-type stock grid.
3. **Manage Stock** — add units to a couple of blood types (e.g. add 20 units of `O+`). Confirm the table updates and the status badge changes from "Out of Stock" to "Healthy" once above 5 units. Try removing more than you have — it should clamp at 0, never go negative.
4. **Fulfill Requests** — you should see the open request the hospital posted in Section 5. Click **Mark Fulfilled**, confirm the dialog, and check it moves into "Recently Fulfilled" below and disappears from the open list. Then check **Manage Stock** again — that blood type's units should have gone down by the request's quantity.
5. **Donation Log** — a read-only, system-wide list of every donation any hospital has logged.

---

## 8. Suggested presentation order (about 12–15 minutes)

1. **Homepage** — show the design, submit the contact form live, point out the thank-you banner.
2. **Register** a fresh donor, showing the live age-validation warning as you type an invalid age.
3. **Try logging in immediately** — show it's blocked pending approval, and the error appears right on the login page rather than a blank screen.
4. **Switch to admin** — show the dashboard stats, approve the donor, then use **Create Staff Account** to create a hospital and a blood bank account live, pointing out these two roles can no longer self-register.
5. **Switch to hospital** — search for the donor, post an urgent request, then log a donation for them.
6. **Switch to donor** — dashboard now shows the donation, history, and the campaign matching their blood type.
7. **Switch to blood bank** — add stock, then fulfill the hospital's open request, showing stock drop automatically.
8. **Switch to recipient** — post a request, leave feedback with the star widget.
9. **Close on the admin table** — Approve/Edit/Delete, all in one place.

That sequence hits registration validation, the approval workflow, hashed login, the new staff-creation model, search + matching, urgent requests, donation logging, inventory management, and feedback — essentially the whole system — in one continuous story.

---

## 9. If something doesn't work

- **Blank page / error about mysqli** → Apache or MySQL isn't running, or the database name doesn't match (`lifelink_db` in `includes/db.php`).
- **"Table doesn't exist" or a `role` enum error** → re-import `schema.sql` — the role list and blood_stock table are new since earlier versions.
- **Login says "incorrect username or password"** → passwords are case-sensitive; double-check what you typed at registration.
- **Search/Campaigns shows nothing** → blood type and location need to actually match between the donor and whoever's searching — blood type must be an exact match (`O+` vs `O-` won't match).
- **Images broken on homepage** → confirm the 5 `.jpg` files are sitting directly in the project folder, not in a subfolder.
- **Can't create a Hospital or Blood Bank account** → that's only possible from the admin's **Create Staff Account** page now, not `register.html`.
