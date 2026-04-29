# FDE Admission Portal 2026–27
## Complete Client System Documentation

**Client:** Federal Directorate of Education (FDE), Islamabad
**System Name:** FDE Admission Portal
**Academic Year:** 2026–27
**Document Version:** 3.0
**Prepared:** April 2026

---

## TABLE OF CONTENTS

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Geographic Structure](#3-geographic-structure)
4. [User Roles & Access Levels](#4-user-roles--access-levels)
5. [Role — Admin](#5-role--admin)
6. [Role — HOI (Head of Institution)](#6-role--hoi-head-of-institution)
7. [Role — AEO (Area Education Officer)](#7-role--aeo-area-education-officer)
8. [Role — Director](#8-role--director)
9. [Role — FDE Cell](#9-role--fde-cell)
10. [Module 01 — Authentication & Account Management](#10-module-01--authentication--account-management)
11. [Module 02 — Admin Panel](#11-module-02--admin-panel)
12. [Module 03 — School Profile Setup](#12-module-03--school-profile-setup)
13. [Module 04 — Facilities Setup](#13-module-04--facilities-setup)
14. [Module 05 — Class & Section Setup](#14-module-05--class--section-setup)
15. [Module 06 — Baseline Enrollment](#15-module-06--baseline-enrollment)
16. [Module 07 — Daily Admissions Entry](#16-module-07--daily-admissions-entry)
17. [Module 08 — Admission Report (HOI)](#17-module-08--admission-report-hoi)
18. [Module 09 — Admission Quota](#18-module-09--admission-quota)
19. [Module 10 — Student Transfers](#19-module-10--student-transfers)
20. [Module 11 — Student Referrals](#20-module-11--student-referrals)
21. [Module 12 — Merit Lists](#21-module-12--merit-lists)
22. [Module 13 — Staff Strength](#22-module-13--staff-strength)
23. [Module 14 — New Construction Rooms](#23-module-14--new-construction-rooms)
24. [Module 15 — Admission Corrections](#24-module-15--admission-corrections)
25. [Module 16 — Admission Edit Grants](#25-module-16--admission-edit-grants)
26. [Module 17 — Seat Configuration (FDE Override)](#26-module-17--seat-configuration-fde-override)
27. [Module 18 — Enrollment Override](#27-module-18--enrollment-override)
28. [Module 19 — Admission Period Control](#28-module-19--admission-period-control)
29. [Module 20 — Monitoring & School Tracking](#29-module-20--monitoring--school-tracking)
30. [Module 21 — Colleges (Model & Ex-FG)](#30-module-21--colleges-model--ex-fg)
31. [Module 22 — UC Control Rooms](#31-module-22--uc-control-rooms)
32. [Module 23 — Reports & Analytics](#32-module-23--reports--analytics)
33. [Module 24 — Audit Log](#33-module-24--audit-log)
34. [Module 25 — Announcements](#34-module-25--announcements)
35. [Module 26 — Notifications](#35-module-26--notifications)
36. [Module 27 — Public Portal](#36-module-27--public-portal)
37. [Module 28 — AI Reports (AI Studio)](#37-module-28--ai-reports-ai-studio)
38. [Module 29 — App Settings](#38-module-29--app-settings)
39. [Module 30 — Theme & Appearance](#39-module-30--theme--appearance)
40. [Module 31 — Portal Settings](#40-module-31--portal-settings)
41. [Module 32 — System Reset](#41-module-32--system-reset)
42. [Special Programs](#42-special-programs)
43. [Dashboard — HOI](#43-dashboard--hoi)
44. [Dashboard — AEO](#44-dashboard--aeo)
45. [Dashboard — Director](#45-dashboard--director)
46. [Dashboard — FDE Cell](#46-dashboard--fde-cell)
47. [Data Flow — End-to-End Summary](#47-data-flow--end-to-end-summary)
48. [Module Quick Reference](#48-module-quick-reference)

---

## 1. Project Overview

### What Is the FDE Admission Portal?

The **FDE Admission Portal 2026–27** is a secure, centralized, web-based school admission management system built exclusively for the **Federal Directorate of Education (FDE), Islamabad**. It replaces paper-based and spreadsheet-based admission tracking with a live digital platform that connects every school head, area officer, and FDE department in one unified system.

### The Problem It Solves

Before this portal, FDE schools submitted admission data manually through paper forms and Excel spreadsheets. This caused:

- No real-time visibility into how many students have been admitted across 100+ schools
- No way to enforce seat capacity limits — schools could over-admit without detection
- No centralized tracking of special programs (OOSC, P2G, Matric Tech)
- Manual data aggregation taking days to produce reports
- Parents had no way to check school availability or seat counts publicly
- Errors and corrections required physical paperwork
- No visibility into staff strength across schools

### What the System Delivers

| Stakeholder | What They Get |
|---|---|
| **HOI / School Head** | A step-by-step digital process: profile setup → facilities → class setup → daily admissions → reports → staff strength |
| **AEO** | Live dashboard for their sector showing seat data, enrollment, admissions, Matric Tech, new rooms |
| **Director** | System-wide read-only dashboard with all statistics |
| **FDE Cell** | Full operational control — reports, corrections, transfers, referrals, seat overrides, period control, announcements, settings |
| **Public / Parents** | A no-login school search portal to find schools, check seats, view merit lists |

---

## 2. Technology Stack

| Component | Technology |
|---|---|
| **Backend Framework** | Laravel 10 (PHP 8.2) |
| **Database** | MySQL |
| **Frontend** | Blade Templating, Alpine.js (reactive UI), Tailwind CSS |
| **Authentication** | Laravel Auth + Spatie Permission (role-based access control) |
| **PDF Export** | Laravel DomPDF (Barryvdh) |
| **Excel Export** | Maatwebsite Laravel Excel |
| **Email** | Laravel Mail (correction decisions, notifications) |
| **Deployment** | Apache / Nginx web server |

### Security Model

- Every route is role-protected via **Spatie Permission middleware**
- HOIs can only see and modify data for their own assigned institution
- AEOs only see institutions within their assigned sector(s)
- All destructive changes (corrections, transfers, seat overrides) are recorded in the audit log
- Password reset via secure email link
- Maintenance mode locks out all non-admin users

---

## 3. Geographic Structure

The system mirrors FDE's administrative geography:

```
FDE (Federal Directorate of Education)
  └── Sectors  (e.g., Urban-I, Urban-II, B-K, Tarnol, Sihala, Nilore, Model Colleges)
        └── Union Councils (UCs)
              └── Schools / Institutions
                    └── Classes (Nursery → Class 12, ECE)
                          └── Sections (A, B, C …)
```

**Sectors** group schools geographically and are the primary unit for AEO assignment and FDE reporting.

**Union Councils** are sub-units within sectors used for HOI profile setup and UC Control Room tracking.

---

## 4. User Roles & Access Levels

The system has **5 distinct roles**:

| Role | Who Uses It | Scope | Key Capabilities |
|---|---|---|---|
| **Admin** | IT / System Administrator | System-wide | Create users, manage schools, configure academic years, bulk import |
| **HOI** | Head of Institution | Own school only | Profile, class setup, admissions, staff strength, corrections, transfers |
| **AEO** | Area Education Officer | Assigned sector(s) | Monitor schools, view class data, admissions, new rooms |
| **Director** | FDE Director | System-wide | Read-only monitoring of all schools and statistics |
| **FDE Cell** | FDE Admission Staff | System-wide | Full control: reports, corrections, transfers, referrals, seat config, settings, announcements |

> **Important:** Roles are strictly enforced. A HOI cannot access another school's data. An AEO cannot access schools outside their sector. Directors cannot edit data.

---

## 5. Role — Admin

### Who

System administrator(s) responsible for configuring the platform before the admission season begins.

### Purpose

Set up the master data that the entire system depends on: geographic structure, schools, users, and the active academic year.

### Capabilities

#### 5.1 User Management
- Create, edit, activate, and deactivate user accounts
- Assign roles: HOI, AEO, FDE Cell, Director
- Link HOI accounts to their institution
- Assign one or more sectors to AEO accounts
- Search and filter by name, email, role, or status
- View summary counts: total HOIs, AEOs, FDE Cell staff, Directors, inactive accounts

#### 5.2 Institution (School) Management
- Register new schools with: name, EMIS code, type, sector, union council, gender, shift
- Edit school details and activate/deactivate schools
- View all schools with filters by sector, type, gender, and status

#### 5.3 Sector Management
- Create, edit, and manage sectors (e.g., Urban-I, Urban-II, Rural, Model Colleges)
- Each sector has a name, code, and active status

#### 5.4 Union Council Management
- Create and manage Union Councils (UCs) linked to sectors
- UCs are used during HOI profile setup and UC Control Room tracking

#### 5.5 Academic Year Management
- Create academic years (e.g., "2026–27")
- Set the active academic year — all admissions and reports are scoped to this year
- Configure admission start date, end date, and daily submission cutoff time

#### 5.6 Bulk Import
- Import schools and users via CSV/Excel file upload
- Useful for initial system setup or mass onboarding

---

## 6. Role — HOI (Head of Institution)

### Who

The principal or head of each FDE school. Each HOI account is linked to exactly one institution.

### Purpose

Enter and maintain all data for their school: initial setup, class configuration, daily admission counts, and staff strength.

### Complete Workflow (Step by Step)

```
Step 1: Profile Setup        → Select UC, sector, school; confirm gender and shift
Step 2: Facilities Setup     → Declare programs (ECE, Matric Tech, evening, transport, etc.)
Step 3: Class & Section Setup→ Configure active classes, seats, sections, existing enrollment
Step 4: Baseline Enrollment  → (Optional) Submit promoted/failed breakdown for FDE review
Step 5: Admission Quota      → (Optional) Set per-class intake quota
Step 6: Daily Admissions     → Enter today's new admissions class by class, every day
Step 7: Admission Report     → View own cumulative admission summary
Step 8: Staff Strength       → Enter teaching and program staff counts
Step 9: Other Modules        → Transfers, corrections, new construction rooms, referrals, merit lists
```

### HOI Module Access Summary

| Module | Access |
|---|---|
| Profile Setup | ✅ First login |
| Facilities | ✅ Edit own school |
| Class & Section Setup | ✅ Edit own school |
| Baseline Enrollment | ✅ Submit, view own |
| Admission Quota | ✅ Set own quotas |
| Daily Admissions | ✅ Enter for own school |
| Admission Report | ✅ View own school |
| Staff Strength | ✅ Enter for own school |
| Merit Lists | ✅ Upload/manage own |
| Monitoring (own school) | ✅ View own history |
| Student Transfers | ✅ Submit, view own |
| Correction Requests | ✅ Submit, view own |
| New Construction Rooms | ✅ View assigned rooms |
| Referrals | ✅ View received, act on them |
| Notifications | ✅ In-app bell notifications |

---

## 7. Role — AEO (Area Education Officer)

### Who

Government officers responsible for education quality in one or more geographic sectors.

### Purpose

Monitor all schools within their assigned sector(s) in real time. AEOs do not enter data — they supervise.

### Key Capabilities

- **Dashboard**: Sector-level summary with seats, existing enrollment, admissions, available capacity, Matric Tech counts, new rooms
- **School-wise table**: Every school in scope with class-by-class seat and enrollment data
- **Matric Tech**: Existing baseline + this year's admissions + total
- **New Construction Rooms**: Total, allocated, remaining
- **Monitoring**: Drill down into any individual school's full admission history

### Multi-Sector Support

An AEO can be assigned to **more than one sector**. The dashboard aggregates data across all assigned sectors automatically.

---

## 8. Role — Director

### Who

Senior FDE Director with system-wide oversight responsibility.

### Purpose

Read-only monitoring of the full system. Directors see all the data FDE Cell sees but cannot make changes.

### Key Capabilities

- Full system-wide statistics (all sectors combined)
- Per-sector breakdown table
- School-level monitoring drill-down
- No create/edit/delete access anywhere

---

## 9. Role — FDE Cell

### Who

FDE admission department staff responsible for running the portal operationally.

### Purpose

Full system control. FDE Cell oversees everything: reports, corrections, transfers, referrals, period management, school overrides, announcements, settings.

### Key Capabilities

| Capability | Description |
|---|---|
| Dashboard | System-wide admission stats with sector breakdown and non-submitting schools list |
| Admission Monitoring | View all school submissions, filter by date/school |
| Admission Corrections | Approve or reject HOI correction requests |
| Admission Edit Grants | Grant/revoke time-limited edit permission for past entries |
| Student Transfers | Create, approve, reject cross-school student transfers |
| Student Referrals | Create and manage student referral letters to schools |
| Seat Configuration | Override school seat counts; lock/unlock seats |
| Enrollment Override | Override a school's existing enrollment directly |
| Admission Period | Set admission dates/cutoff; open/close admissions |
| Staff Strength | View and verify all schools' staff strength registers |
| Colleges | View Model Colleges and Ex-FG Colleges with admission stats |
| UC Control Rooms | View Union Council control room details and contacts |
| Reports & Analytics | Master, sector, gender, OOSC, vacancy reports; PDF/Excel |
| AI Studio | Natural-language data queries |
| Audit Log | Full change log |
| Announcements | Create/manage system-wide notices for HOIs and other roles |
| App Settings | Configure app name, logo, branding, support info, maintenance mode |
| Theme | Customize portal colors, fonts, sidebar, dark/light mode |
| Portal Settings | Configure public portal announcement, contact info |
| System Reset | Full data wipe and re-seed (super admin only) |

---

## 10. Module 01 — Authentication & Account Management

### Purpose

Secure login, password management, and role-based access control for all users.

### Features

#### Login
- Email + password authentication
- Role-based redirect after login:
  - HOI → HOI Dashboard (or Profile Setup if first login)
  - AEO → AEO Dashboard
  - Director → Director Dashboard
  - FDE Cell → FDE Cell Dashboard
  - Admin → Admin Panel

#### Forgot Password
- User enters registered email
- System sends a secure password reset link via email
- Link expires after a set time

#### Reset Password
- User sets a new password via the emailed link

#### Account Activation
- Admin can activate or deactivate any user account
- Inactive users receive a clear "account inactive" message on login

#### Maintenance Mode
- When maintenance mode is ON (set in App Settings), all non-admin users see a maintenance page
- Admins can still log in during maintenance

#### Session Security
- Sessions expire on inactivity
- CSRF protection on all forms

---

## 11. Module 02 — Admin Panel

### Purpose

System configuration and master data management. Used only by system administrators.

### Sub-Modules

#### 11.1 User Management

**Purpose:** Create and manage all user accounts.

**Features:**
- Create users with name, email, phone, password, and role
- Assign HOI users to their institution
- Assign AEO users to one or more sectors (multi-sector pivot)
- Edit user details, reset passwords, activate/deactivate
- Filter by role, status, school name, or text search

**Stats Shown:**
- Count by role: HOIs, AEOs, FDE Cell, Directors
- Inactive accounts count

#### 11.2 Institution (School) Management

**Fields per School:**
- Name, EMIS code
- Type: Primary, Elementary, Middle, High, Higher Secondary, Model College, Ex-FG College
- Sector and Union Council
- Gender: Boys / Girls / Co-education
- Shift: Morning / Evening / Both
- Active status

**Features:**
- Create, edit, view school profiles
- Filter by sector, type, gender, status

#### 11.3 Sector Management

Manage geographic sectors. Each has a name, code (used in public portal), and active flag.

#### 11.4 Union Council (UC) Management

Manage UCs within sectors. Used in HOI profile setup and UC Control Room tracking.

#### 11.5 Academic Year Management

**Fields:**
- Year name (e.g., "2026–27")
- Admission start date
- Admission end date
- Daily cutoff time (time by which schools must submit daily)
- Active flag — only one year is active at a time

All admissions, reports, and statistics are automatically scoped to the active academic year.

#### 11.6 Bulk Import

Import schools and users in bulk via CSV/Excel. Used for initial system setup.

---

## 12. Module 03 — School Profile Setup

### Who Uses It

HOI — first login only.

### Purpose

Link the HOI's account to their school and confirm basic school attributes.

### How It Works

1. HOI logs in for the first time
2. System shows the Profile Setup form (cannot proceed without completing this)
3. HOI selects:
   - **Union Council** → auto-fills Sector via AJAX
   - **Sector** → loads School dropdown via AJAX
   - **School** from the dropdown
   - **Gender** of the school: Boys / Girls / Co-education
   - **Shift**: Morning / Evening / Both
4. On submit, the user is linked to that institution
5. Redirected to the HOI Dashboard

### Notes

- A HOI can only link to one school; re-setup is not permitted once linked
- Gender and shift on the Institution record are updated at this step

---

## 13. Module 04 — Facilities Setup

### Who Uses It

HOI — after profile setup.

### Purpose

Declare which special programs and physical facilities the school has. These flags control which fields appear throughout the rest of the system.

### Facility Toggles

| Facility | Effect on System |
|---|---|
| **ECE Center** | Adds ECE-I and ECE-II/Prep classes to class setup and daily admissions |
| **Matric Tech Program** | Adds Matric Tech existing count (Class 9 & 10) and daily Matric Tech field |
| **Evening Classes** | Splits all class and admission data into Morning / Evening columns |
| **Transport Service** | Shown as badge on Public Portal; filterable by parents |
| **Meal Program** | Shown as badge on Public Portal |
| **Cambridge Program** | Shown as badge on Public Portal; filterable |
| **Is Cambridge School** | Full Cambridge school flag |

### Important Behaviors

- **Enabling Evening after class setup**: The system pre-fills morning columns from existing combined totals — no data is lost.
- **Disabling Evening**: The system safely collapses evening data back into combined totals — morning data is never wiped.

---

## 14. Module 05 — Class & Section Setup

### Who Uses It

HOI — after facilities setup.

### Purpose

Configure which classes the school runs, seat counts, existing enrollment, and sections.

### Non-Evening School

For each active class:
- **Existing Students** — students already enrolled from last year
- **Total Seats** — total authorized capacity
- **Available Seats** — auto-calculated: Total − Existing
- **Sections** — comma-separated names (e.g., A,B,C); defaults to A

### Evening / Dual-Shift School

Per class, data is split:
- Morning Existing / Evening Existing
- Morning Seats / Evening Seats
- Morning Available / Evening Available (calculated)

### Matric Tech Section

If `has_matric_tech = true`, a dedicated teal-bordered section appears below the main table for **Class 9 and Class 10 only**:
- **Matric Tech Existing** — previous year's Matric Tech student count
- Validated: must not exceed the class's total existing students

### ECE Classes

A separate ECE section at the top for ECE-I and ECE-II/Prep with same seat/existing structure.

### Validation

- Total Seats ≥ Existing Students (enforced per class)
- Matric Tech Existing ≤ Class Existing Students
- Sections auto-cleaned and uppercased

### Saving

- All InstitutionClass records created or updated
- All InstitutionSection records deleted and recreated fresh
- `classes_configured = true` set on institution — unlocks daily admissions

---

## 15. Module 06 — Baseline Enrollment

### Who Uses It

HOI (enter) + FDE Cell (verify)

### Purpose

Submit the detailed breakdown of existing students — promoted and failed counts by class — for formal FDE verification.

### How It Works

1. HOI enters promoted and failed counts per class (and per shift for evening schools)
2. System validates: promoted + failed = existing_enrollment
3. HOI submits — status becomes `submitted`
4. FDE Cell reviews and either verifies or returns with a note

### Status Flow

```
Draft → Submitted → Verified / Returned → (if Returned) Revised → Re-submitted
```

### Notes

- Once verified, HOI cannot edit the baseline
- FDE can return a submission with a note if figures seem incorrect
- Feeds into the master report's existing enrollment totals

---

## 16. Module 07 — Daily Admissions Entry

### Who Uses It

HOI — every working day during the admission period.

### Purpose

The core data entry module. HOI enters how many new students were admitted today, class by class, shift by shift, and by special program.

### Prerequisites

- School profile must be set up
- Classes must be configured (`classes_configured = true`)
- Admission period must be open

### Entry Form Layout

For each active class, HOI enters:

**Non-Evening School:**

| Field | Description |
|---|---|
| Morning Boys | Regular male admissions |
| Morning Girls | Regular female admissions |
| OOSC Boys / Girls | Out-of-School Children |
| P2G Boys / Girls | P2G program admissions |
| Matric Tech Count | New Matric Tech (Class 9 & 10 only, if applicable) |
| Available Seats | Real-time remaining capacity |
| Cumulative Total | Running total admitted so far this year |

**Evening / Dual-Shift School:**
All fields above split into **Morning** and **Evening** columns.

### Seat Capacity Protection

- Available seats are shown live and recalculate as numbers are entered
- Over-admission is blocked: save is rejected with a clear error message
- Capacity formula: Total Seats − Existing Enrollment − Previously Admitted This Year

### Date Selection

- Defaults to today's date
- HOI can select a past date (within academic year) to enter missed data
- Future dates are blocked
- Past locked entries can only be edited if FDE grants an **Edit Grant**

### Daily Reminder

If HOI opens the form today and has not yet submitted, a reminder banner shows the cutoff time.

### Status Flow

```
Draft (saved) → Submitted → Verified → Locked
```

---

## 17. Module 08 — Admission Report (HOI)

### Who Uses It

HOI

### Purpose

Complete cumulative summary of all admissions for the school in the current academic year.

### What It Shows

- Per-class: existing enrollment, total seats, admitted this year (morning/evening boys/girls, OOSC, P2G, Matric Tech)
- Daily history: date-by-date log of all submissions
- Totals row at bottom

### Export

- PDF export of the full report

### Vacancy Report

A sub-view showing classes with remaining available seats.

---

## 18. Module 09 — Admission Quota

### Who Uses It

HOI (set quotas) — viewed alongside daily admission data

### Purpose

Allow a school to optionally set a **soft per-class intake quota** — a target maximum for new admissions in each class. This does not hard-block entries but provides visibility into how many slots remain against the school's own internal target.

### How It Works

1. HOI opens Admission Quota page
2. For each active class, enters an optional quota number
3. System shows:
   - Quota set
   - Admitted so far this year (per class)
   - Remaining against quota: `max(0, Quota − Admitted)`
4. Grand totals: Total Quota / Total Admitted / Total Remaining

### Notes

- Quota is stored in `institution_classes.admission_quota`
- A blank quota means no limit set for that class
- This is a planning tool — it does not replace the hard seat-capacity limit

---

## 19. Module 10 — Student Transfers

### Who Uses It

- **HOI**: Submit transfer requests; view own school's transfer history
- **FDE Cell**: Create transfers; approve/reject/cancel; view all transfers system-wide

### Purpose

Manage the formal movement of students from one FDE school to another, with automatic enrollment count adjustments.

### Transfer Workflow

#### FDE Cell Creates:
1. Selects **From School** and **To School**
2. Enters per-student details: Class, Student Name, Father Name, Notes
3. Multiple students transferred in one batch
4. System validates both schools have the class configured
5. Transfer records created with status `pending`

#### Actions:

| Action | Effect |
|---|---|
| **Accept** | Decrements existing_enrollment at sending school; increments at receiving school |
| **Reject** | Transfer declined with reason |
| **Cancel** | Transfer cancelled before action |

#### Cross-Sector Transfers

If both schools are in different sectors, the transfer is flagged **cross-sector**. These require an additional **cross-sector approval** with mandatory justification note before final accept/reject.

### Status Flow

```
pending → accepted / rejected / cancelled
(cross-sector: pending → cross_sector_approved → accepted / rejected)
```

### Filters (FDE View)

By school, class, student name, date range, sector, cross-sector flag, status

---

## 20. Module 11 — Student Referrals

### Who Uses It

- **FDE Cell**: Create referrals, manage, re-refer rejected ones
- **HOI**: View referrals received, accept/reject, record test and admission outcome

### Purpose

FDE Cell formally refers a student to a specific school for admission. The school head sees the referral and acts on it.

### Referral Workflow

#### FDE Cell Creates:
1. Selects target school, class, student name/father name, gender, shift, notes
2. System generates unique **Reference Number** (e.g., `REF-2026-0042`)
3. Status: `pending`

#### HOI Acts:
- **Accept** → student processed for admission
- **Reject** → declined with reason

#### Re-Referral:
- If rejected, FDE Cell can re-refer the student to a different school
- Original referral marked `re_referred`; new referral linked to original

### Status Flow

```
pending → accepted / rejected
          ↑ if rejected → FDE can create re_referred
closed (FDE cancels)
```

### Stats (FDE Dashboard)

Total / Pending / Accepted / Rejected / Re-referred / Admitted / Not Admitted / Test Failed

---

## 21. Module 12 — Merit Lists

### Who Uses It

- **HOI**: Upload merit list files for their school
- **FDE Cell**: View and manage merit lists across all schools
- **Public**: Download merit lists without login

### Purpose

Schools running merit-based admissions upload PDF/Excel merit list files. These appear on the Public Portal for parents.

### HOI Features

- Upload one or multiple merit list files (PDF, XLSX, CSV) per school
- Add a title/label for each file
- Delete uploaded files
- Files stored securely in public storage

### Public Portal Display

- A "Merit List" badge appears on school cards when merit lists are uploaded
- Dedicated Merit Lists page lists all schools with uploaded files and download buttons

---

## 22. Module 13 — Staff Strength

### Who Uses It

- **HOI**: Enter teaching and program staff data for their school
- **FDE Cell**: View all schools' registers, verify/lock, export PDF and Excel

### Purpose

Record the full staff strength of every school — how many sanctioned posts exist, how many are filled, on leave, deputationist, daily wager, etc. This gives FDE a complete staffing picture across the system.

### Data Entry (HOI)

HOI fills a register with two sections:

#### Teaching Staff (based on school level/type)

| Column | Description |
|---|---|
| Post Type | (e.g., Principal, Vice Principal, SST, EST, PST, PET, etc.) |
| Sanctioned Posts | Total authorized positions |
| Filled Posts | Currently occupied positions |
| Sacked Employees | Employees removed/dismissed |
| Daily Wagers (In) | Daily wage staff added |
| Daily Wagers (Out) | Daily wage staff who left |
| Study Leave | On study leave |
| Deputationist (In) | Staff on deputation to this school |
| Deputationist (Out) | Staff on deputation from this school |
| Temporary (In) | Temporary staff added |
| Temporary (Out) | Temporary staff who left |
| Number of Posts | Effective working count |

#### Program Staff

Same columns for program-specific staff (non-teaching roles tied to special programs).

### Register Status Flow

```
Draft → Submitted → Verified / Returned → Locked
```

### HOI Actions

- Save as draft (can re-edit)
- Submit for FDE review

### FDE Cell Actions

- View all registers across all schools
- Filter by sector, school type, submission status
- View individual school register with full entry breakdown
- Verify or Return a submitted register
- Lock a verified register (no further edits)
- **Export to PDF** and **Export to Excel** for reporting

---

## 23. Module 14 — New Construction Rooms

### Who Uses It

- **HOI**: View new rooms assigned to their school; allocate rooms to classes
- **FDE Cell**: Record new rooms for schools; track status; view all schools
- **AEO / Director**: View new room stats on dashboard

### Purpose

Track new classrooms added through construction projects, and record how rooms are allocated to classes.

### Data Per School

| Field | Description |
|---|---|
| Rooms Total | Total new rooms constructed/under construction |
| Rooms Allocated | Rooms assigned to specific classes |
| Construction Status | `pending` / `near_completion` / `completed` |
| Notes | Any relevant construction notes |

### Allocation

HOI allocates new rooms to specific classes once construction is complete.

### FDE Dashboard Stats

- Schools with new rooms
- Total new rooms
- Completed rooms
- Near-completion count
- Estimated capacity added (rooms × 40 students)

---

## 24. Module 15 — Admission Corrections

### Who Uses It

- **HOI**: Submit correction requests for wrong past entries
- **FDE Cell**: Review, approve, or reject requests

### Purpose

Once a daily admission entry is submitted, it is locked for editing. If a HOI made an error, they submit a **Correction Request** — they cannot edit directly.

### HOI Submits Correction

1. Selects the date and class to correct
2. System shows current (old) values
3. HOI enters new correct values and a reason
4. Status: `pending`

### FDE Cell Reviews

- Side-by-side comparison of **Old Values vs New Values**
- **Approve**: The daily_admissions record is updated; existing_enrollment is adjusted by the net difference
- **Reject**: Correction declined with an FDE note

### Email Notification

HOI receives an **email** when their correction request is approved or rejected, including the FDE Cell's note.

### Audit

Every correction approval/rejection is recorded in the Audit Log with full old/new value history.

---

## 25. Module 16 — Admission Edit Grants

### Who Uses It

FDE Cell

### Purpose

A HOI can only edit today's admission entry. If they need to correct a **past** entry that is already locked, FDE Cell must grant them a **time-limited edit permission** — an Edit Grant. Without this grant, the HOI must use the Correction Request workflow instead.

### How It Works

1. FDE Cell opens the Admission Edit Grants page
2. Selects a school and specifies:
   - **Date From / Date To** — which past dates the HOI can edit
   - **Expires At** — exact date/time when the grant expires
   - **Reason** — why the edit is being allowed
3. Grant is created with status `active`
4. HOI can now edit entries for those specific past dates until the grant expires

### Grant Statuses

| Status | Meaning |
|---|---|
| `active` | Grant is live; HOI can edit the specified past dates |
| `expired` | Grant has passed its expiry datetime — auto-expired by the system |
| `revoked` | FDE Cell manually revoked the grant before expiry |

### Revoke

FDE Cell can revoke any active grant at any time by providing a revocation reason.

### Filters

By school, sector, status, expiring-soon flag, date range

### Audit

All grant creations and revocations are recorded in the Audit Log.

---

## 26. Module 17 — Seat Configuration (FDE Override)

### Who Uses It

FDE Cell

### Purpose

View and override the seat counts that schools configured themselves. FDE Cell has final authority over seat numbers.

### Features

#### Index View

- All schools with configured seat totals
- Locked vs unlocked status
- Filter by school name, locked status

#### Per-School Edit

- View each class's current total seats and existing enrollment
- Override `total_seats` for any class
- Add an override reason note
- Override recorded with who made it and when (`overridden_by`, `override_reason`, `overridden_at`)

#### Lock / Unlock Seats

- **Lock**: Prevents the HOI from changing seat counts again
- **Unlock**: Re-enables HOI editing
- Lock timestamp and locking officer are recorded on the institution

### Summary Stats

- System total seats
- Count of locked vs unlocked schools

---

## 27. Module 18 — Enrollment Override

### Who Uses It

FDE Cell

### Purpose

Allow FDE Cell to directly override a school's `existing_enrollment` on a per-class basis if the baseline data was submitted incorrectly and a correction is needed at the class configuration level (rather than at the daily admission level).

### How It Works

1. FDE Cell selects a school
2. System shows each class with:
   - Current existing_enrollment
   - Total seats
   - Available seats (after verified admissions)
   - Section count
3. FDE Cell can update existing_enrollment values directly
4. FDE Cell can **unlock** a submitted/verified enrollment if it needs to be revised

### Use Case

This is used when the HOI's existing enrollment figures were wrong at the class setup stage and have already been verified — a scenario where Admission Corrections cannot fix the issue because it predates daily submissions.

---

## 28. Module 19 — Admission Period Control

### Who Uses It

FDE Cell

### Purpose

Control when admissions are open, how long they run, and what the daily submission cutoff time is.

### Settings Per Academic Year

| Setting | Description |
|---|---|
| **Admission Start Date** | First day schools can enter admissions |
| **Admission End Date** | Last day schools can enter admissions |
| **Daily Cutoff Time** | Time each day (e.g., 2:00 PM) by which schools should submit |

### Actions

| Action | Effect |
|---|---|
| **Open Admissions** | Sets all active institutions to `admission_status = open` |
| **Close Admissions** | Sets all active institutions to `admission_status = closed` |
| **Update Period** | Changes start/end dates and cutoff time |

### Live Stats on Page

- Total active schools
- Schools submitted today
- Days elapsed since admission start
- Days remaining until admission end
- Whether admissions are currently open
- Whether today's cutoff has passed

---

## 29. Module 20 — Monitoring & School Tracking

### Who Uses It

- **HOI**: View own school's submission history
- **AEO**: Monitor all schools in their sector
- **Director**: Monitor all schools system-wide
- **FDE Cell**: Monitor all schools system-wide

### Purpose

Allow supervisory roles to view the detailed admission history of any school, class by class, date by date.

### School List View (AEO / Director / FDE)

- Table of all schools in scope
- Per school: total seats, existing enrollment, admitted this year, available seats
- Color-coded submission status indicators
- Filter by school name, sector, submission status

### School Detail View

For a selected school:
- Full school profile information
- Class-wise seat configuration table
- Cumulative admissions by class and shift
- Daily submission log (every date with counts)
- Status of each entry (draft, submitted, verified, locked)

### Non-Submitting Schools (FDE Dashboard)

Live list of schools that have NOT submitted today's data, grouped by sector. Used by FDE Cell to follow up with schools.

---

## 30. Module 21 — Colleges (Model & Ex-FG)

### Who Uses It

- **FDE Cell / AEO / Director**: View college lists and admission stats
- **HOI of a College**: Redirected to their own college profile

### Purpose

Provide dedicated list and profile pages for **Model Colleges** and **Ex-FG Colleges** — a distinct institution type that operates differently from regular schools but still enters admissions through the same portal.

### Features

#### Model Colleges List
- Searchable/filterable list of all active Model Colleges
- Per college: name, sector, UC, current HOI
- Admission stats: Total Boys Admitted / Total Girls Admitted / Total Admitted (this academic year)
- Grand totals row at top

#### Ex-FG Colleges List
- Same layout as Model Colleges but for Ex-FG College type

#### College Profile Page
- Full institution details (sector, UC, type, gender)
- Current HOI name
- Grand totals: boys, girls, total admitted
- Class-wise admission breakdown (class → boys / girls / total)

#### PDF Export
- Landscape PDF export of the college list (with filters applied)

---

## 31. Module 22 — UC Control Rooms

### Who Uses It

FDE Cell

### Purpose

Track **Union Council Control Rooms** — each UC has a designated control room with specific focal persons, organizations, and FDE contacts assigned to it. This module records and displays all 32 UC control room details in one searchable directory.

### Data per UC Control Room

| Field | Description |
|---|---|
| Union Council | Which UC this control room serves |
| FDE School Name | FDE school hosting the control room |
| Organization Name | Organization running the control room (e.g., NCHD) |
| Focal Person Name | Contact person at the control room |
| Focal Person Phone | Contact number |
| NCHD FO Name | NCHD Field Officer name |
| NCHD FO Phone | NCHD Field Officer contact |
| FDE Focal Person Name | FDE's assigned focal person |
| FDE Focal Person Phone | FDE focal person's contact |
| Notes | Any additional notes |

### Features

- Searchable by UC name/code, school name, focal person, or NCHD FO
- Filter by organization
- View detail card for any individual UC control room
- **PDF Export** — landscape A4 export of all filtered records

---

## 32. Module 23 — Reports & Analytics

### Who Uses It

FDE Cell (full access), Director (read-only view)

### Purpose

Generate comprehensive, filterable reports on admission data across all schools.

### 32.1 Master Report

**The most comprehensive report** — school-by-school, class-by-class breakdown.

**Filters:**
- Sector, school type, school gender, date range, class level (All / ECE / Non-ECE)

**Columns per row (per school per class):**
Existing enrollment, total seats, available seats, morning boys, morning girls, evening boys, evening girls, OOSC boys, OOSC girls, P2G boys, P2G girls, Matric Tech count, total admitted

**Export:** PDF

### 32.2 Sector Report

Summary by sector: seats, existing enrollment, admitted, available, OOSC, P2G, Matric Tech totals per sector.

### 32.3 Gender Report

Analyzes admissions by student gender across schools and classes. Compares male vs female counts per sector.

### 32.4 OOSC Report

Tracks Out-of-School Children admissions specifically. OOSC boys and girls per school and class, with cumulative totals.

### 32.5 Vacancy Report

Identifies schools with remaining available seats. Shows: school, sector, total seats, existing, admitted, available, fill percentage.

**Also available to HOI** (scoped to own school).

### 32.6 Report Dashboard

Visual overview with charts:
- Admissions by sector (bar chart)
- Daily admission trend (line chart)
- Special program breakdown (pie/donut chart)

### 32.7 Schools Report

Summary list of all schools with their admission status, seat configuration status, submission counts, and last submission date. Useful for tracking which schools are active and up-to-date.

---

## 33. Module 24 — Audit Log

### Who Uses It

FDE Cell

### Purpose

A complete tamper-evident log of all significant actions in the system.

### What Is Logged

- Every admission correction approval/rejection (with old and new values)
- Seat overrides and locks
- Edit grant creations and revocations
- Profile and enrollment changes
- Status changes on any record
- User who performed the action, their role at the time, and exact timestamp

### Fields per Log Entry

| Field | Description |
|---|---|
| Action | What was done (approved, rejected, updated, locked, etc.) |
| Model / Record | Which entity was affected |
| Old Values | Data before the change |
| New Values | Data after the change |
| Changed By | User name and role at time of change |
| Institution | Which school the change relates to |
| Timestamp | Exact date and time |

### Filters

By user, role, field changed, institution, date range

### Stats

- Total audit entries today
- Total entries in system

---

## 34. Module 25 — Announcements

### Who Uses It

FDE Cell (create/manage) — All logged-in users (view)

### Purpose

FDE Cell publishes system-wide announcements that appear inside the portal for HOIs, AEOs, Directors, or all roles. Used for important notices like admission deadline reminders, policy changes, or system updates.

### Announcement Fields

| Field | Description |
|---|---|
| Title | Short headline |
| Body | Full announcement text (up to 2,000 characters) |
| Type | `info` / `warning` / `success` / `danger` — controls color/icon |
| Priority | `normal` / `high` / `urgent` |
| Is Active | Whether the announcement is currently live |
| Is Pinned | Pinned announcements appear at the top of the list |
| Published At | Optional: schedule for future publish date |
| Expires At | Optional: auto-hide after this date |
| Target Roles | Which roles see this: HOI / AEO / FDE Cell / Director (blank = all) |

### FDE Cell Actions

- Create, edit, delete announcements
- Pin/unpin
- Activate/deactivate

### User Experience

- Logged-in users see active announcements relevant to their role
- Pinned announcements appear first
- Expired announcements are automatically hidden

---

## 35. Module 26 — Notifications

### Who Uses It

HOI (primary recipient) — all logged-in users

### Purpose

In-app notification system that alerts HOIs about important events related to their school — such as admission correction decisions, referral actions, and system messages.

### Features

- **Notification Bell** in the top navigation bar showing unread count
- Full notifications list page (paginated, newest first)
- Mark a single notification as read
- Mark all notifications as read at once
- Delete individual notifications

### Triggered By

- Admission correction approved or rejected by FDE Cell
- Referral sent to the HOI's school
- Any other automated system event

### Behavior

- All notifications are marked as read automatically when the HOI opens the notifications list page
- AJAX support for marking individual notifications without page reload

---

## 36. Module 27 — Public Portal

### Who Uses It

**No login required** — parents, students, and the general public.

### Purpose

Allow anyone to search for FDE schools, check seat availability, and download merit lists without needing an account.

### 36.1 School Search Page

**Filters Available:**
- School name or EMIS code (text search)
- Sector, school type, gender, shift
- Special filters: Has transport / Has meal program / Has Matric Tech / Has evening classes / Is Cambridge / Has ECE

**School Cards Show:**
- Name, sector, type, gender
- Program badges (ECE, Matric Tech, Cambridge, Transport, Meal, Evening)
- Merit List badge if files are uploaded
- Total seats / Existing / Admitted this year / **Available seats**

**Hero Stats (top of page):**
- Total FDE schools
- Schools with available seats
- Total available seats system-wide
- Total students admitted this year

### 36.2 School Detail Page

Clicking a school card shows:
- Full profile (name, sector, type, gender, shift)
- Class-wise seat table: Total Seats | Existing | Admitted This Year | Available
- For evening schools: Morning and Evening columns shown separately
- Merit list download links if available

### 36.3 Seat Availability Page

Available seats broken down by area:
- **Urban Schools** (Urban-I + Urban-II)
- **Rural Schools** (B-K, Tarnol, Sihala, Nilore)
- **Model Colleges**

Each area shows: Total seats / Existing / Available + Morning/Evening breakdown. Drill-down shows all schools in that area with available seats.

### 36.4 Merit Lists Page

Lists all schools with uploaded merit list files and download buttons.

---

## 37. Module 28 — AI Reports (AI Studio)

### Who Uses It

FDE Cell

### Purpose

Query admission data using **natural language questions** instead of navigating reports manually.

### How It Works

1. FDE Cell types a plain-English question
2. The AI agent queries the relevant data and returns an answer
3. Results include tables, summaries, and comparisons

### Example Questions

- "Which schools have not submitted today?"
- "How many OOSC students were admitted in Class 1 this month?"
- "Show me the top 10 schools by total admissions this year"
- "What is the total available capacity in Model Colleges?"

---

## 38. Module 29 — App Settings

### Who Uses It

FDE Cell (portal.settings permission required)

### Purpose

Configure the portal's branding, identity, contact information, and operational mode — all stored in the database and applied system-wide instantly without code changes.

### Settings Available

| Setting | Description |
|---|---|
| **App Name** | The name shown in the browser title bar and sidebar header |
| **App Tagline** | Subtitle shown below the app name |
| **Sidebar Footer Text** | Small text at the bottom of the sidebar |
| **Primary Color** | Main brand color (hex code) used throughout the UI |
| **Secondary Color** | Accent color (hex code) |
| **Support Email** | Contact email shown on help pages |
| **Support Phone** | Contact phone shown on help pages |
| **App Logo** | Upload custom logo (PNG, JPG, SVG, WebP — max 2MB) |
| **App Favicon** | Upload custom browser favicon (PNG, ICO — max 512KB) |
| **Show Public Portal** | Toggle: show or hide the public-facing portal |
| **Maintenance Mode** | Toggle: put the portal in maintenance mode for all non-admin users |
| **Maintenance Message** | Custom message shown to users during maintenance |

### Logo & Favicon

- Old logo/favicon files are automatically deleted when a new one is uploaded
- A "Remove" button deletes the current logo/favicon without uploading a replacement
- Files stored in the `public/app/branding` directory

---

## 39. Module 30 — Theme & Appearance

### Who Uses It

FDE Cell (portal.settings permission required)

### Purpose

Fully customize the visual appearance of the entire portal — colors, typography, sidebar dimensions, card styles, and the default light/dark mode — all without touching code.

### Customizable Settings

#### Colors

| Setting | Default | Description |
|---|---|---|
| Primary Color | `#4bad46` | Main action color (buttons, active links) |
| Secondary Color | `#28a745` | Secondary accent color |
| Dark BG | `#0a0e27` | Dark mode background |
| Dark Card | `#1a1f3a` | Dark mode card background |
| Dark Sidebar BG | `#0d1235` | Dark mode sidebar background |
| Light BG | `#f0f2fb` | Light mode page background |
| Light Card | `#ffffff` | Light mode card background |
| Light Sidebar BG | `#ffffff` | Light mode sidebar background |
| Active Text (Dark) | `#6dda67` | Active link text in dark mode |
| Active Text (Light) | `#1a6617` | Active link text in light mode |

#### Layout

| Setting | Default | Description |
|---|---|---|
| Sidebar Width | 260 px | Width of the left navigation sidebar |
| Sidebar Font Size | 13 px | Text size in sidebar menu items |
| Sidebar Link Padding | 8.5 px | Top/bottom padding on each sidebar link |
| Topbar Height | 58 px | Height of the top navigation bar |
| Topbar Font Size | 15 px | Title text size in topbar |
| Card Border Radius | 12 px | Rounded corner radius for cards |
| Small Radius | 8 px | Smaller radius for buttons and inputs |
| Card Padding | 20 px | Internal padding for card components |

#### Typography

| Setting | Default | Options |
|---|---|---|
| Font Family | Inter | Inter, Roboto, Poppins, DM Sans, Nunito, Outfit |
| Base Font Size | 14 px | 12–18 px |

#### Default Mode

| Setting | Default | Options |
|---|---|---|
| Default Mode | Dark | Dark / Light |

### Reset to Defaults

A single "Reset to Defaults" button restores all theme settings to the factory defaults.

---

## 40. Module 31 — Portal Settings

### Who Uses It

FDE Cell

### Purpose

Configure the content that appears on the **Public Portal** (the no-login parent-facing portal) — announcements, contact details, and portal-level on/off control.

### Settings

- **Public Announcement Text** — shown at the top of the portal landing page (e.g., "Admissions are now open for 2026–27")
- **Contact Information** — email and phone shown to parents on the portal
- **Portal Open/Closed** — toggle to show/hide the portal entirely (different from App Settings maintenance mode)

---

## 41. Module 32 — System Reset

### Who Uses It

FDE Cell (super admin only — requires `portal.settings` permission)

### Purpose

A nuclear option to completely wipe all system data and re-seed the database from scratch. Used at the end of an academic year to prepare for the next year's admission cycle.

### What It Does

1. Deletes all data from every table in a safe FK-respecting order:
   - Daily admissions, corrections, edit grants, referrals, transfers
   - Enrollment records, class configurations, sections
   - Audit logs, session data, cache
   - Institutions, users, roles, permissions
   - Sectors, UCs, classes, academic years
   - App settings

2. Clears all Laravel caches (config, route, view, application)

3. Runs all database seeders to re-establish base data:
   - Roles & permissions
   - Class definitions
   - Academic year
   - Admin user
   - Union councils, sectors, institutions

4. Logs out the current user (their session is wiped)

### Safety Mechanism

- Requires typing exactly: **`RESET SYSTEM`** in a confirmation field
- The action is fully logged before execution
- Cannot be undone — all data is permanently deleted

### Warning

This is a **destructive, irreversible** action. It should only be used at the start of a new academic year when all previous data has been exported and archived.

---

## 42. Special Programs

### 42.1 OOSC — Out-of-School Children

**Fields:** OOSC Boys + OOSC Girls (split by Morning / Evening for evening schools)

**Appears in:** Daily form (always), Master Report, OOSC Report, FDE dashboard cumulative total

### 42.2 P2G — Path to Growth

**Fields:** P2G Boys + P2G Girls (split by Morning / Evening for evening schools)

**Appears in:** Daily form, Master Report, FDE dashboard cumulative total

### 42.3 Matric Tech Program

**Eligible Classes:** Class 9 and Class 10 only

**Enabled By:** `has_matric_tech = true` on the school (set in Facilities)

| Data Point | Where | Description |
|---|---|---|
| **Matric Tech Existing** | Class Setup | Previous year's Matric Tech students (baseline) |
| **Matric Tech Count** | Daily Admissions | New Matric Tech students admitted today |

**Dashboard Display (all roles): three figures:**
1. Existing / Prev. Year
2. This Year / New Admits (with today's sub-count)
3. Total / Combined

**Constraint:** Matric Tech Existing cannot exceed total existing students for Class 9 or 10

### 42.4 ECE — Early Childhood Education

**Classes:** ECE-I (age 3–4) and ECE-II / Prep (age 4–5)

**Enabled By:** `has_ece = true` (set via ECE toggle in Class Setup)

ECE classes appear as a separate section in Class Setup and as separate rows in the daily form. Filterable in/out of the Master Report.

### 42.5 Cambridge Program

**Enabled By:** `is_cambridge = true`

"Cambridge" badge on Public Portal school card. Filterable in portal search.

### 42.6 Dual-Shift (Evening Classes)

**Enabled By:** `has_evening_classes = true`

| Module | Effect |
|---|---|
| Class Setup | All fields split: Morning Existing / Evening Existing / Morning Seats / Evening Seats |
| Daily Admissions | Morning Boys/Girls + Evening Boys/Girls as separate fields |
| HOI Dashboard | Morning Admitted and Evening Admitted shown separately |
| AEO / Director / FDE Dashboards | Sub-labels show "Morning X · Evening Y" |
| Public Portal | Per-shift seat tables on school detail page |
| Master Report | Per-shift columns in export |

**Data Safety:** Disabling evening never wipes morning data — the system collapses both into combined totals safely.

---

## 43. Dashboard — HOI

### Purpose

The HOI's home page showing their school's full admission status at a glance.

### Sections

#### Setup Checklist

A visual progress indicator showing which setup steps are complete:
- Profile Setup ✅
- Facilities configured ✅
- Classes & Sections configured ✅
- Baseline Enrollment submitted ✅

Each step is a clickable link. Incomplete steps are highlighted in amber/red.

#### Summary Cards (4 main cards)

| Card | Value |
|---|---|
| Intake Capacity | Total authorized seats across all active classes |
| Existing Enrollment | Students already enrolled (previous year) |
| Newly Admitted | Total students admitted this academic year |
| Seats Available | Total − Existing − Admitted |

For evening schools, each card shows "Morning X · Evening Y" sub-labels.

#### Matric Tech Cards (if applicable)

| Card | Value |
|---|---|
| Matric Tech Existing | Previous year's Matric Tech students |
| Admitted This Year | New Matric Tech (with today's sub-count) |
| Total | Existing + Admitted |

#### Class-Wise Admission Table

Every active class with:
- Class name
- Existing Students (total; plus shift sub-labels for evening schools)
- Intake Capacity (total seats)
- Newly Admitted (this year; plus shift breakdown)
- Available Seats

#### Quick Actions

- Enter Today's Admissions
- View Full Report
- Edit Class Setup

---

## 44. Dashboard — AEO

### Purpose

Real-time overview of all schools in the AEO's assigned sector(s).

### Sections

#### Grand Summary Cards

| Card | Value |
|---|---|
| Schools | Total active schools in scope |
| Total Seats | Sum of all seats |
| Existing Enrollment | Total pre-existing students |
| Admitted This Year | Cumulative new admissions |
| Available Seats | Total remaining capacity |
| Total Enrollment | Existing + Admitted |

#### Matric Tech Card

Three-figure breakdown:
- Existing / Prev. Year
- This Year / New Admits (with today's sub-count)
- Total / Combined

#### New Construction Rooms Card

- Total new rooms / Allocated / Remaining / Schools with new rooms

#### Sector Summary Table (Multi-Sector AEOs)

Summary row per sector: school count, seats, existing, admitted, available, Matric Tech, new rooms.

#### School-Wise Detail Table

Full table of every school with:
- School name, sector
- Total seats / existing / admitted / available
- Class-level breakdown and section counts

---

## 45. Dashboard — Director

### Purpose

System-wide read-only monitoring for the FDE Director.

### Content

Identical to the AEO Dashboard in layout but scoped to **all sectors** (full system). The Director sees:

- All schools across all sectors aggregated
- Per-sector breakdown table
- Matric Tech system-wide totals
- New construction rooms system-wide
- Links to school-level monitoring

All data is read-only — no edit or action buttons anywhere.

---

## 46. Dashboard — FDE Cell

### Purpose

Operational command center for the FDE admission team.

### Sections

#### Today's Stats (5 cards)

| Card | Value |
|---|---|
| Total Today | All students admitted today (all programs) |
| Regular (Today) | Morning + Evening regular admissions today |
| OOSC (Today) | Out-of-School Children admitted today |
| P2G (Today) | P2G program today |
| Matric Tech (Today) | Matric Tech admissions today |

#### Cumulative Stats Row

Same 5 categories but for the **entire academic year**.

#### Matric Tech Breakdown Row (3 cards)

- Matric Tech Existing (prev. year baseline)
- Matric Tech This Year (with today sub-count)
- Matric Tech Total (Existing + This Year)

#### School Submission Status

| Stat | Value |
|---|---|
| Total Schools | All active schools |
| Submitted Today | Schools that submitted data today |
| Not Submitted | Schools that have not submitted today |

#### Sector-Wise Breakdown Table

Per sector: cumulative total / today's total / OOSC / P2G / Matric Tech counts.

#### Schools Not Submitted Today

Full list of non-submitting schools grouped by sector, for follow-up.

#### Available Capacity

System-wide: Total Seats − Existing Enrollment − Total Admitted.

#### New Construction Rooms

- Schools with new rooms / Total rooms / Completed / Near completion / Estimated capacity

#### Referral Stats

Total referrals / Pending / Accepted / Rejected / Admitted / Not Admitted / Test Failed

---

## 47. Data Flow — End-to-End Summary

```
ADMIN SETUP
     │
     ├── Creates Sectors, UCs, Institutions (Schools)
     ├── Creates User Accounts → assigns roles
     └── Creates Academic Year → sets active year + admission period

          │
          ▼
HOI SETUP SEQUENCE
     │
     ├── Profile Setup     → Links HOI account to school
     ├── Facilities        → Declares programs (Evening, ECE, Matric Tech, etc.)
     ├── Class Setup       → Configures classes, sections, seats, existing enrollment
     │                        └── Matric Tech existing count (Class 9 & 10)
     ├── Baseline Enroll.  → Submits promoted/failed breakdown → FDE verifies
     ├── Admission Quota   → (Optional) Sets per-class intake quotas
     └── Staff Strength    → Enters teaching and program staff data → FDE verifies

          │
          ▼
DAILY ADMISSION CYCLE (every working day)
     │
     ├── HOI opens daily form → sees classes with live capacity
     ├── Enters: Morning/Evening × Boys/Girls × Regular/OOSC/P2G/MatricTech
     ├── System validates capacity in real time
     ├── HOI saves → status = draft (can re-edit)
     ├── HOI submits → status = submitted
     └── AEO / FDE Cell can see submitted data immediately

          │
          ▼
MONITORING & MANAGEMENT (continuous)
     │
     ├── AEO monitors sector schools live on dashboard
     ├── FDE Cell monitors all schools, identifies non-submitters
     ├── FDE Cell issues Announcements to notify all HOIs of changes
     ├── HOI submits Correction Requests → FDE reviews → email notification
     ├── FDE Cell issues Edit Grants for time-limited past-entry edits
     ├── FDE Cell creates Student Transfers → adjusts enrollment counts
     ├── FDE Cell issues Student Referrals → HOI acts on them
     ├── FDE Cell overrides seats or enrollment if needed → recorded in audit
     └── FDE Cell verifies Staff Strength registers → locks when complete

          │
          ▼
REPORTING (on demand)
     │
     ├── HOI → Admission Report + Vacancy Report (own school)
     ├── FDE → Master Report, Sector, Gender, OOSC, Vacancy, Schools Report
     ├── FDE → Staff Strength export (PDF + Excel)
     ├── FDE → College lists export (PDF)
     ├── FDE → UC Control Rooms PDF export
     ├── FDE → AI Studio natural-language queries
     └── All major reports → PDF / Excel export

          │
          ▼
PUBLIC PORTAL (always live, no login)
     │
     ├── Parents search schools by filters
     ├── School cards show available seats live
     ├── School detail pages show per-class seat data
     ├── Seat Availability page: Urban / Rural / Model breakdown
     └── Merit Lists page for schools with uploaded merit list PDFs
```

---

## 48. Module Quick Reference

| # | Module Name | Who Uses It | Purpose |
|---|---|---|---|
| 01 | Authentication | All roles | Login, logout, password reset, maintenance mode |
| 02 | Admin Panel | Admin | Users, schools, sectors, UCs, academic years, import |
| 03 | School Profile Setup | HOI | Link HOI to school, confirm gender/shift |
| 04 | Facilities Setup | HOI | Declare programs (ECE, Matric Tech, evening, etc.) |
| 05 | Class & Section Setup | HOI | Configure active classes, seats, sections, existing enrollment |
| 06 | Baseline Enrollment | HOI + FDE | Submit/verify promoted & failed student breakdown |
| 07 | Daily Admissions | HOI | Enter new student counts per class per day |
| 08 | Admission Report | HOI + FDE | View cumulative admission summary; PDF export |
| 09 | Admission Quota | HOI | Set per-class soft intake quotas |
| 10 | Student Transfers | HOI + FDE | Move students between schools; auto-adjust enrollment |
| 11 | Student Referrals | FDE (create) + HOI (act) | FDE formally refers a student to a specific school |
| 12 | Merit Lists | HOI (upload) + Public (download) | Upload and publish merit list files |
| 13 | Staff Strength | HOI (enter) + FDE (verify/export) | Record and verify teaching and program staff counts |
| 14 | New Construction Rooms | HOI (allocate) + FDE (manage) | Track new classrooms and class allocation |
| 15 | Admission Corrections | HOI (request) + FDE (approve/reject) | Correct submitted admission data with FDE approval |
| 16 | Admission Edit Grants | FDE | Grant/revoke time-limited permission to edit past entries |
| 17 | Seat Configuration | FDE | Override school seat counts; lock/unlock seats |
| 18 | Enrollment Override | FDE | Override a school's per-class existing enrollment directly |
| 19 | Admission Period | FDE | Set admission dates/cutoff; open/close admissions system-wide |
| 20 | Monitoring | HOI + AEO + Director + FDE | School-level admission history and live tracking |
| 21 | Colleges | FDE + HOI (own) | Model Colleges and Ex-FG Colleges list, profile, and PDF export |
| 22 | UC Control Rooms | FDE | Directory of UC control room focal persons and contacts |
| 23 | Reports & Analytics | FDE + Director | Master, sector, gender, OOSC, vacancy, schools reports; PDF/Excel |
| 24 | Audit Log | FDE | Tamper-evident log of all system changes |
| 25 | Announcements | FDE (create) + All (view) | System-wide notices targeted by role |
| 26 | Notifications | HOI (receive) | In-app bell notifications for correction decisions and referrals |
| 27 | Public Portal | Public (no login) | School search, seat availability, merit lists |
| 28 | AI Studio | FDE | Natural-language data queries |
| 29 | App Settings | FDE | Branding, logo, colors, support info, maintenance mode |
| 30 | Theme & Appearance | FDE | Colors, fonts, sidebar, dark/light mode customization |
| 31 | Portal Settings | FDE | Public portal announcement and contact configuration |
| 32 | System Reset | FDE (super admin) | Full data wipe and re-seed for new academic year |

---

## Special Programs Quick Reference

| Program | Flag on School | Classes Affected | Extra Fields |
|---|---|---|---|
| OOSC | Always available | All classes | OOSC Boys / Girls (per shift) in daily form |
| P2G | Always available | All classes | P2G Boys / Girls (per shift) in daily form |
| Matric Tech | `has_matric_tech` | Class 9 & 10 only | Existing count (setup) + Daily count (admissions) |
| ECE | `has_ece` | ECE-I, ECE-II/Prep | Full seat/enrollment setup + daily admissions |
| Evening / Dual Shift | `has_evening_classes` | All classes | Splits all fields into Morning + Evening everywhere |
| Cambridge | `is_cambridge` | N/A | Badge on public portal; filterable in portal search |

---

*This document covers the complete FDE Admission Portal 2026–27 system with all 32 modules.*
*For technical support or system configuration, contact the system administrator.*

*Document prepared by: Development Team | Last updated: April 2026 | Version 3.0*
