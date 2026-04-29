# FDE Admission Portal 2026–27
## Complete System Documentation

**Client:** Federal Directorate of Education (FDE), Islamabad
**Academic Year:** 2026–27
**Document Version:** 1.0
**Date:** April 2026

---

## TABLE OF CONTENTS

1. [Project Overview](#1-project-overview)
2. [System Architecture & Technology](#2-system-architecture--technology)
3. [User Roles & Access Levels](#3-user-roles--access-levels)
4. [Module 1 — Authentication & Account Management](#4-module-1--authentication--account-management)
5. [Module 2 — Admin Panel](#5-module-2--admin-panel)
6. [Module 3 — School Profile Setup (HOI)](#6-module-3--school-profile-setup-hoi)
7. [Module 4 — Facilities Setup (HOI)](#7-module-4--facilities-setup-hoi)
8. [Module 5 — Class & Section Setup (HOI)](#8-module-5--class--section-setup-hoi)
9. [Module 6 — Baseline Enrollment (HOI)](#9-module-6--baseline-enrollment-hoi)
10. [Module 7 — Daily Admissions Entry (HOI)](#10-module-7--daily-admissions-entry-hoi)
11. [Module 8 — Admission Report (HOI)](#11-module-8--admission-report-hoi)
12. [Module 9 — Monitoring (HOI / AEO / Director)](#12-module-9--monitoring-hoi--aeo--director)
13. [Module 10 — Student Transfers](#13-module-10--student-transfers)
14. [Module 11 — Student Referrals](#14-module-11--student-referrals)
15. [Module 12 — Merit Lists](#15-module-12--merit-lists)
16. [Module 13 — Staff Strength](#16-module-13--staff-strength)
17. [Module 14 — New Construction Rooms](#17-module-14--new-construction-rooms)
18. [Module 15 — Admission Corrections](#18-module-15--admission-corrections)
19. [Module 16 — FDE Reports & Analytics](#19-module-16--fde-reports--analytics)
20. [Module 17 — Seat Configuration (FDE Override)](#20-module-17--seat-configuration-fde-override)
21. [Module 18 — Admission Period Control](#21-module-18--admission-period-control)
22. [Module 19 — Audit Log](#22-module-19--audit-log)
23. [Module 20 — Public Portal](#23-module-20--public-portal)
24. [Module 21 — HOI Dashboard](#24-module-21--hoi-dashboard)
25. [Module 22 — AEO / Director Dashboard](#25-module-22--aeo--director-dashboard)
26. [Module 23 — FDE Cell Dashboard](#26-module-23--fde-cell-dashboard)
27. [Special Programs](#27-special-programs)
28. [Data Flow Summary](#28-data-flow-summary)

---

## 1. Project Overview

The **FDE Admission Portal 2026–27** is a centralized, web-based school admission management system built for the **Federal Directorate of Education (FDE), Islamabad**. It digitizes and streamlines the entire admission process across all FDE-managed schools — from initial class setup by school heads, to daily admission data entry, real-time monitoring by supervisors, and public-facing school search for parents and students.

### Problem It Solves

Previously, FDE schools submitted admission data manually through paper forms and spreadsheets, making it difficult to:
- Track real-time admission progress across 100+ schools
- Enforce seat capacity limits
- Monitor special programs (OOSC, P2G, Matric Tech)
- Provide accurate, timely data to decision-makers
- Allow parents to check school availability publicly

### What the System Does

- **Schools (HOI)** enter their daily admissions online, class by class
- **Area Education Officers (AEO)** monitor their sector schools in real time
- **FDE Cell** oversees all schools, generates reports, manages corrections, and controls the admission period
- **Directors** view system-wide enrollment statistics
- **Public / Parents** can search for schools and check seat availability without logging in

---

## 2. System Architecture & Technology

| Component | Technology |
|---|---|
| **Backend Framework** | Laravel 10 (PHP 8.2) |
| **Database** | MySQL |
| **Frontend** | Blade Templates, Alpine.js, Tailwind CSS |
| **Authentication** | Laravel Auth with Spatie Permission (role-based) |
| **Deployment** | Web server (Apache/Nginx), live + local environments |
| **Export** | PDF / Excel exports for all major reports |

### Geographic Structure

```
FDE (System-wide)
  └── Sectors (Urban-I, Urban-II, Rural, Model Colleges, etc.)
        └── Union Councils (UCs)
              └── Schools (Institutions)
                    └── Classes & Sections
```

---

## 3. User Roles & Access Levels

The system has **5 distinct roles**, each with a strictly defined scope:

| Role | Who | Scope | Access |
|---|---|---|---|
| **Admin** | System Administrator | System-wide | Full system configuration |
| **HOI** | Head of Institution | One school | Own school data only |
| **AEO** | Area Education Officer | Assigned sector(s) | Read + monitor assigned schools |
| **FDE Cell** | FDE Admission Staff | System-wide | Full operational control |
| **Director** | FDE Director | System-wide | Read-only monitoring |

### Role-Based Navigation
Each role sees a completely different dashboard and sidebar menu. No role can access another role's data unless explicitly permitted.

---

## 4. Module 1 — Authentication & Account Management

### Purpose
Secure login system with role-based access control. Every user is tied to a specific role and (for HOIs) a specific school.

### Features

**Login**
- Email and password authentication
- Role detection on login → redirected to correct dashboard
- Failed login protection

**Forgot Password**
- Email-based password reset link
- Secure token with expiry

**Account Management (Admin)**
- Create user accounts and assign roles
- Assign HOI users to specific schools
- Assign AEO users to one or more sectors
- Activate / deactivate accounts

---

## 5. Module 2 — Admin Panel

### Purpose
System-level configuration tool for the system administrator. Controls the master data that all other modules depend on.

### Sub-Modules

#### 2.1 Academic Year Management
- Create and manage academic years (e.g., 2026–27)
- Set admission window: start date, end date, daily cutoff time
- Mark one year as **active** (system-wide)
- Only data from the active year appears in dashboards and reports

#### 2.2 Institution (School) Management
- Add, edit, delete schools
- School attributes: Name, Code, Type (I-V / I-VIII / I-X / I-XII / VI-VIII / VI-X / VI-XII / XI-XII / XI-XIV / Model College), Gender (Boys / Girls / Co-Education), Shift (Morning / Evening / Both), Address
- Assign school to a Union Council (sector is auto-derived)
- Toggle facilities: Transport, Meal Program, Matric Tech, Evening Classes
- Admission status per school: Not Started / Open / Closed / By Approval
- Cambridge status (auto-set based on school name)
- Active / inactive toggle

#### 2.3 Sector Management
- Create and manage sectors (Urban-I, Urban-II, Tarnol, B-K, Sihala, Nilore, Model Colleges, etc.)
- Each sector has a name, code, and active status

#### 2.4 Union Council Management
- Create Union Councils and assign them to sectors
- Each school is assigned to a UC (sector is auto-derived from the UC)

#### 2.5 User Management
- Create HOI, AEO, FDE Cell, Director accounts
- Assign school to HOI user
- Assign sector(s) to AEO user
- Reset passwords, edit roles

#### 2.6 Data Import
- Bulk import schools and users via CSV/Excel files
- Useful for initial system setup

---

## 6. Module 3 — School Profile Setup (HOI)

### Purpose
First-time setup step for every school. The HOI must complete the school profile before accessing any other module.

### Who Uses It
HOI (Head of Institution)

### Features
- View and confirm school name, type, gender, shift, sector
- This data is managed by Admin; HOI views it for confirmation
- System enforces completion before admissions can begin

### Workflow
```
HOI logs in → Profile incomplete? → Redirect to Profile Setup → Confirm details → Access granted
```

---

## 7. Module 4 — Facilities Setup (HOI)

### Purpose
Allows each school to declare which special facilities and programs it runs. These settings unlock or hide relevant features across the system.

### Who Uses It
HOI

### Facilities That Can Be Toggled

| Facility | Effect When Enabled |
|---|---|
| **Transport** | School appears in transport filter on Public Portal |
| **Meal Program** | School appears in meal program filter |
| **Matric Tech** | Matric Tech section appears in Class Setup and Daily Admissions |
| **Evening Classes** | Splits all class data into Morning + Evening shifts throughout the system |

### Important Behaviors
- **Enabling Evening Classes** → Class Setup form changes to per-shift layout (Morning Seats + Evening Seats separately)
- **Disabling Evening Classes** → System automatically resets per-shift columns back to morning-only; combined totals are preserved (no data loss)
- The `shift` field (Morning / Evening / Both) on the institution stays in sync with the Evening Classes toggle automatically

---

## 8. Module 5 — Class & Section Setup (HOI)

### Purpose
Defines which classes are active at the school, how many sections each has, how many seats are authorized, and how many students were already enrolled from the previous year.

### Who Uses It
HOI (set once per academic year, can be updated)

### What Is Configured

| Field | Description |
|---|---|
| **Active/Inactive** | Toggle which classes are offered at this school |
| **Existing Students** | Students already enrolled from the previous year (baseline) |
| **Total Seats** | Total intake capacity authorized for this class |
| **Available Seats** | Auto-calculated: Total Seats − Existing − Admitted So Far |
| **Sections** | Comma-separated section names (e.g., A, B, C) |

### Evening School Variant
When the school has Evening Classes enabled, each class gets **separate fields** for:
- Morning Existing / Evening Existing
- Morning Seats / Evening Seats
- Morning Available / Evening Available (auto-calculated per shift)

### ECE (Early Childhood Education)
- School can toggle whether it has an ECE center
- If yes, ECE-I and ECE-II classes appear as additional rows

### Matric Tech (Class 9 & 10 only)
- When Matric Tech is enabled, a separate table appears for Class 9 and Class 10
- HOI enters **Matric Tech Existing** (previous year Matric Tech students)
- Must not exceed total existing students for that class

### Validation Rules
- Total Seats must be ≥ Existing Students (cannot have more existing than capacity)
- Matric Tech Existing must be ≤ Existing Enrollment for that class
- At least 1 section defaults to "A" if none entered

---

## 9. Module 6 — Baseline Enrollment (HOI)

### Purpose
Records the starting enrollment figures — students already present at the school before the new admission cycle begins. This is used as the baseline against which new admissions are measured.

### Who Uses It
HOI

### Features
- Enter existing enrollment per class
- Promoted students count
- Failed / Repeater students count
- For evening schools: separate morning and evening figures
- Data feeds into the Available Seats calculation throughout the system

---

## 10. Module 7 — Daily Admissions Entry (HOI)

### Purpose
The core operational module. Each school enters the number of new students admitted on a given day, class by class, gender by gender, shift by shift.

### Who Uses It
HOI

### How It Works

**Step 1 — Select Date**
HOI selects the date (defaults to today). Past dates can be entered (with audit trail). Future dates are blocked.

**Step 2 — Enter Admission Counts**
For each active class, HOI fills in:

| Category | Boys | Girls |
|---|---|---|
| **Regular** (Morning shift) | ✓ | ✓ |
| **Regular** (Evening shift) | ✓ | ✓ |
| **OOSC** — Out-of-School Children | ✓ | ✓ |
| **P2G** — Private to Government | ✓ | ✓ |

**Step 3 — Matric Tech Count** (Class 9 & 10 only, if enabled)
A separate count of students admitted under the Matric Tech program.

**Step 4 — Save or Submit**
- **Save as Draft** — saves progress, does not lock
- **Submit** — finalizes the day's entry; if school is set to "By Approval" mode, entry goes to submitted status awaiting FDE verification; otherwise it is auto-verified

### Key Rules & Guards
- **Seat Capacity Enforcement**: System rejects entries that exceed available seats (Total Seats − Existing − Prior Admissions)
- **Evening Shift Guard**: For evening schools, morning and evening seats are checked independently
- **Status Flow**: Draft → Submitted → Verified → (FDE can Lock)
- **Past Date Editing**: HOI can correct past entries; all changes are audit-logged
- **Locked Entries**: FDE-locked entries cannot be changed by HOI

### Visual Indicators
- Available seats shown in green (seats remaining) or red (full/over)
- Status badge per class row (Draft / Submitted / Verified / Locked)
- Running today's total at top of form

---

## 11. Module 8 — Admission Report (HOI)

### Purpose
Gives the HOI a cumulative view of all admissions entered so far in the academic year, broken down by class, shift, and admission type.

### Who Uses It
HOI

### Features
- Date range filter (default: full academic year)
- Class-wise breakdown: Regular (Boys/Girls), OOSC, P2G, Matric Tech, Grand Total
- Morning vs Evening breakdown for evening schools
- Per-shift seat availability per class
- Grand total row at bottom
- Vacancy report: which classes still have open seats

---

## 12. Module 9 — Monitoring (HOI / AEO / Director)

### Purpose
Tracks each admitted student through a defined post-admission workflow: test verification → merit list → document verification → final enrollment confirmation.

### Who Uses It
- **HOI** — Enters test results and tracks each student's status
- **AEO** — Views monitoring progress across sector schools
- **Director** — System-wide view of workflow completion

### Workflow Stages

```
Admitted → Test Verification → Merit Status → Document Check → Confirmed
```

| Stage | Fields Tracked |
|---|---|
| **Test Verification** | Whether student appeared for entry test, result (Pass/Fail) |
| **Merit Status** | Pending / Shortlisted / Selected / Rejected |
| **Document Status** | Pending / Submitted / Verified / Incomplete |
| **Final** | Fully enrolled, withdrawn, transferred |

### Features
- One monitoring record per daily admission entry per class
- HOI can update test results in bulk
- Color-coded status indicators
- AEO and Director see aggregated progress across all schools

---

## 13. Module 10 — Student Transfers

### Purpose
Manages the formal process of a student moving from one FDE school to another within the system.

### Who Uses It
- **HOI** — Initiates a transfer request for a student at their school
- **FDE Cell** — Reviews, approves or rejects transfer requests; views all system-wide transfers

### Workflow
```
HOI requests transfer → FDE Cell reviews → Approved / Rejected
→ (If approved) Student removed from source school, added to destination school
```

### Features
- Transfer request includes: Student name, class, source school, destination school, reason
- FDE can add notes and comments
- Audit trail maintained for all transfer decisions
- HOI can see status of their own transfer requests

---

## 14. Module 11 — Student Referrals

### Purpose
FDE Cell refers specific students (e.g., from OOSC campaigns, special cases) directly to a school. The HOI at the destination school reviews the referral and accepts or rejects the student.

### Who Uses It
- **FDE Cell** — Creates referrals, monitors outcomes
- **HOI** — Receives and acts on referrals

### Referral Workflow
```
FDE creates referral (student details + school + class)
→ HOI notified → HOI accepts / rejects
→ If accepted: HOI conducts test → records result
→ If admitted: counts toward daily admission data
→ FDE tracks outcome: Admitted / Not Admitted / Test Failed
```

### Fields per Referral
- Student name, father's name, gender
- Target school and class
- Shift preference (Morning/Evening)
- Reason for referral
- Reference number (auto-generated)
- Status chain: Pending → Accepted/Rejected → Test result → Admission outcome

### FDE Features
- View all referrals with filters (status, school, class, sector, date range)
- Re-refer a rejected student to a different school
- Cancel a pending referral
- Export referral data

---

## 15. Module 12 — Merit Lists

### Purpose
Schools upload their official merit/selection lists (PDF or Excel files) which are then made publicly visible through the Public Portal.

### Who Uses It
- **HOI** — Uploads merit list files for their school
- **Public / Parents** — Can download merit lists from the portal without logging in

### Features
- Multiple files per school (can upload one per class or one combined)
- File name and upload date displayed
- Direct download link on Public Portal
- Merit list badge shown on school cards in portal search results

---

## 16. Module 13 — Staff Strength

### Purpose
Records the academic and non-academic staff count at each school. Provides FDE with human resource data alongside enrollment data.

### Who Uses It
HOI

### Data Captured
- Number of permanent teachers (male/female)
- Number of contract/ad-hoc teachers
- Administrative staff count
- Support staff count

---

## 17. Module 14 — New Construction Rooms

### Purpose
Tracks newly constructed classrooms at each school. These rooms add to the effective capacity of the school and are tracked separately from the regular seat configuration.

### Who Uses It
- **HOI** — Declares new rooms at their school
- **FDE Cell** — Views system-wide construction progress; allocates rooms

### Features per School
- Number of new rooms constructed
- Construction status: Planned / Under Construction / Near Completion / Completed
- Rooms allocated vs. unallocated
- Estimated enrollment capacity added (rooms × 40 students per room)

### FDE Dashboard Stats
- Total new rooms across all schools
- Schools with new construction
- Rooms completed vs. near completion
- Total extra capacity added

---

## 18. Module 15 — Admission Corrections

### Purpose
Formal mechanism to correct previously submitted admission data. Used when a school realizes they entered wrong numbers after submission.

### Who Uses It
- **HOI** — Submits a correction request with the corrected figures and a reason
- **FDE Cell** — Reviews, approves or rejects corrections; can apply corrections directly

### Workflow
```
HOI submits correction request (old values + new values + reason)
→ FDE reviews → Approved: data corrected in system; Rejected: original data kept
```

### Features
- Correction request shows exact before/after values per field
- FDE can add notes
- Full audit trail: who requested, who approved, when
- HOI notified of outcome

---

## 19. Module 16 — FDE Reports & Analytics

### Purpose
Comprehensive reporting suite for the FDE Cell, giving system-wide visibility into admission progress across all schools and sectors.

### Who Uses It
FDE Cell, AEO (scoped to sector), Director (system-wide read-only)

### Reports Available

#### 16.1 Master Report
- All schools in one table
- Per school: Total Seats, Existing Enrollment, Seats Available, Total Admitted, OOSC, P2G
- Filters: Sector, School Type, Gender, Class, Date Range
- Export to PDF and Excel

#### 16.2 Gender Report
- Admission breakdown by gender (Boys vs Girls) across all schools
- Per sector and per class
- Regular vs OOSC vs P2G breakdown
- Date range filter

#### 16.3 Sector-wise Report
- Summary table grouped by sector
- Each sector: schools count, seats, existing enrollment, new admissions, OOSC, P2G, Matric Tech
- Exportable

#### 16.4 OOSC Campaign Report
- Focused on Out-of-School Children admissions
- Breakdown: Boys/Girls per class, per school, per sector
- Progress tracking against targets

#### 16.5 Vacancy Report
- Which schools and classes still have seats available
- Seats remaining per class
- Useful for guiding student referrals to schools with space

#### 16.6 Report Dashboard
- Summary view of all report types in one place
- Quick stats: total admitted today, total this year, OOSC total, P2G total, Matric Tech total
- Schools not submitted today (with list)
- Sector-by-sector breakdown table

---

## 20. Module 17 — Seat Configuration (FDE Override)

### Purpose
Allows the FDE Cell to directly view and override seat allocations for any school. Used when a school's capacity needs to be adjusted by central authority rather than waiting for the HOI to update it.

### Who Uses It
FDE Cell

### Features
- View all active classes across all schools with their current seat figures
- Edit total seats per class for any school
- Changes are audit-logged with reason
- Override is recorded separately from HOI-entered figures

---

## 21. Module 18 — Admission Period Control

### Purpose
Controls when admissions are open system-wide and for individual schools.

### Who Uses It
FDE Cell (Admin configures the academic year dates)

### Controls Available

**System-wide (Academic Year)**
- Admission start date — from this date, all not-started schools auto-open
- Admission end date — after this date, window closes
- Daily cutoff time — HOI reminder shown before this time daily

**Per-School Override**
- FDE can set individual school status independently:
  - **Open** — accepting admissions
  - **Closed** — blocked from entering admissions
  - **By Approval** — HOI can submit but FDE must approve each day's entry
  - **Not Started** — awaiting start date

### Portal Settings
FDE Cell can configure what appears on the Public Portal:
- Portal title and welcome message
- Whether the portal is publicly visible
- Contact information shown to parents

---

## 22. Module 19 — Audit Log

### Purpose
Complete record of every significant action taken in the system. Provides accountability and traceability for all data changes.

### Who Uses It
FDE Cell (view-only)

### What Is Logged
- Institution created / updated / deleted
- Admission data submitted / corrected / reset
- Transfer requests approved/rejected
- Referrals created/actioned
- Quota changes
- Seat configuration overrides
- Enrollment overrides
- FDE Cell resets of school data

### Log Entry Contains
- Action type
- Who performed the action (user name + role)
- Timestamp
- Old values (before change)
- New values (after change)
- Reason (when provided)
- Related school

### Features
- Filter by action type, date range, school, user
- View detail of any log entry (before/after diff)

---

## 23. Module 20 — Public Portal

### Purpose
A publicly accessible website (no login required) where parents and students can search for schools, check seat availability, and download merit lists.

### Who Uses It
General public — parents, students, guardians

### Features

#### School Search & Discovery
- Search by school name or code
- Filter by: Sector, School Type, Gender, Shift, Class availability
- Filter by facilities: Transport, Meal Program, Matric Tech, Cambridge, ECE
- By default, only schools with available seats are shown
- Vacancy filter: Has Seats / Nearly Full (≥80% filled) / Full

#### School Cards
Each school in results shows:
- School name, type, gender, shift
- Sector name
- Available seats (remaining)
- Merit List badge (if uploaded)
- Total seats vs. admitted

#### School Detail Page
Click any school to see:
- Full school profile (address, type, gender, shift, facilities)
- Class-wise seat availability table
- Morning and Evening breakdown for dual-shift schools
- Available seats per class
- Admission totals per class
- Downloadable merit lists (if uploaded)

#### Seat Availability Overview Page
- System-wide summary broken down by area:
  - **Urban Schools** (Urban-I, Urban-II sectors)
  - **Rural Schools** (Tarnol, B-K, Sihala, Nilore sectors)
  - **Model Colleges**
- Per area: Total seats, filled, available
- Morning vs Evening breakdown
- Per-sector details within each area
- Drill down to school list within each area

#### Hero Statistics (Portal Homepage)
- Total FDE Schools
- Total Seats Available System-wide
- Schools currently accepting admissions
- Total Students Admitted This Year

---

## 24. Module 21 — HOI Dashboard

### Purpose
The HOI's home screen — a real-time snapshot of their school's enrollment status for the current academic year.

### Who Uses It
HOI

### What Is Displayed

**Row 1 — Capacity Overview** (4 cards)
| Card | Shows |
|---|---|
| Intake Capacity | Total authorized seats (with Morning/Evening split for dual-shift schools) |
| Existing Enrollment | Students from previous year (baseline) |
| Seats Available | Remaining seats (auto-calculated) |
| Total Enrollment | Existing + All new admissions so far |

**Row 2 — Admission Breakdown**
- *Evening School*: 4 cards — Morning Admitted (cumulative) · Evening Admitted · Today Morning · Today Evening
- *Morning-only School*: 2 cards — Total Admitted (cumulative) · Today's Admissions

**Row 3 — Matric Tech** (only if school has Matric Tech enabled)
- Matric Tech Existing (previous year baseline — Class 9 & 10)
- Admitted This Year (new Matric Tech students with today's count as sub-label)
- Total Matric Tech (Existing + This Year)

**Class-wise Table**
- One row per active class
- Columns: Class | Sections | Existing Enrollment | Intake Capacity | Seats Available | Morning Admitted | Evening Admitted (if evening school) | Total Enrollment
- Sub-labels show Morning/Evening breakdown for dual-shift schools

**Quick Action Cards** (bottom)
- Baseline Enrollment → link to enrollment entry
- Today's Admissions → link to daily entry form
- Cumulative Admissions → link to full report

---

## 25. Module 22 — AEO / Director Dashboard

### Purpose
Sector-level overview for Area Education Officers. Directors see system-wide view using the same layout.

### Who Uses It
AEO (scoped to assigned sectors), Director (all sectors)

### What Is Displayed

**Grand Summary Row** (6 cards)
- Schools count | Intake Capacity | Promoted Students | Seats Available | Newly Admitted | Total Enrollment

**Matric Tech Card**
- Today's Matric Tech count
- Matric Tech Existing (previous year baseline — scoped to visible institutions)
- Admitted This Year
- Total Matric Tech (Existing + This Year)

**New Construction Rooms Card**
- Total new rooms | Allocated | Available | Schools with new construction

**Sector Summary Table**
- One row per sector with: Schools | Seats | Promoted Students | Available | Admitted | Total | Matric Tech | New Rooms

**School-wise Breakdown** (expandable per sector)
- Expandable table — click a school row to expand per-class detail
- Columns: Sections | Seats | Promoted Students | Available | Admitted | Total
- Shows Morning/Evening sub-rows for dual-shift schools

---

## 26. Module 23 — FDE Cell Dashboard

### Purpose
System-wide real-time command center for the FDE Admission Cell.

### Who Uses It
FDE Cell

### What Is Displayed

**Today's Totals Row** (5 cards)
- Today's Admissions | Regular | OOSC | P2G | Matric Tech (today)

**Matric Tech Breakdown Row** (3 cards)
- Matric Tech Existing (system-wide baseline)
- Admitted This Year (new Matric Tech)
- Total Matric Tech

**Cumulative Totals Row** (6 cards)
- Total Admitted (Year) | Regular | OOSC Campaign | Private to Government | Matric Tech (Year) | Available Capacity

**Referral Outcomes Panel**
- Total referrals | Pending | Accepted | Rejected | Admitted | Not Admitted

**New Construction Rooms Panel**
- Total new rooms | Colleges vs Schools | Enrollment capacity added

**Sector-wise Breakdown Table**
- Per sector: Schools | Today's admissions | Year Total | OOSC | P2G | Matric Tech

**Schools Not Submitted Today**
- List of schools that have not entered any data today (with count badge)
- Direct links to each school's report page

---

## 27. Special Programs

### 27.1 OOSC — Out-of-School Children Campaign
**Purpose:** Track students who were previously out of the formal school system and are now being enrolled.

- Entered separately from regular admissions (Boys/Girls columns for Morning and Evening)
- Tracked in all reports at every level (school, sector, system-wide)
- Appears on public portal statistics
- FDE has dedicated OOSC report with sector and class breakdown

### 27.2 P2G — Private to Government
**Purpose:** Track students transferring from private schools to government schools.

- Separate Boys/Girls count per shift
- All reports show P2G figures alongside regular admissions
- Helps measure effectiveness of government school improvement initiatives

### 27.3 Matric Tech Program
**Purpose:** Vocational/technical stream available alongside regular matriculation at Class 9 and Class 10.

- Only applicable to schools with `has_matric_tech` enabled
- Separate existing count (previous year baseline) stored in Class Setup
- Daily count entered as part of each day's admission entry (for Class 9 and 10)
- Displayed in all dashboards (HOI, AEO, FDE, Director) as: **Existing | Admitted This Year | Total**
- Matric Tech count ≤ total regular admitted for that class (cannot exceed regular count)

### 27.4 ECE — Early Childhood Education
**Purpose:** Track admissions at the pre-primary level.

- Schools can declare they have an ECE center in Class Setup
- ECE-I and ECE-II appear as separate class rows
- Filtered and searchable on Public Portal
- Counted in all enrollment totals

### 27.5 Cambridge Program
**Purpose:** Identifies schools running the Cambridge (O-Level/A-Level) curriculum.

- Cambridge status is auto-assigned based on school name by the system
- Filterable on FDE Schools Report
- Appears on Public Portal with Cambridge filter option

### 27.6 Morning / Evening Dual-Shift Schools
**Purpose:** Some FDE schools run two separate shifts — a morning school and an evening school in the same building.

- Enabled per school via Facilities → Evening Classes
- All data is split into morning and evening throughout:
  - Class Setup: separate seats and existing enrollment per shift
  - Daily Admissions: separate Boys/Girls entry per shift
  - All dashboards: Morning and Evening columns/sub-labels
  - Reports: Per-shift breakdown
  - Public Portal: Shows both morning and evening available seats
- `shift` field (Morning / Both) and `has_evening_classes` boolean are always kept in sync

---

## 28. Data Flow Summary

```
ACADEMIC YEAR CREATED (Admin)
        │
        ▼
SCHOOL PROFILE CONFIRMED (HOI)
        │
        ▼
FACILITIES SET (HOI) ──────────────────────────────────── Evening / Matric Tech / ECE flags set
        │
        ▼
CLASS & SECTION SETUP (HOI) ────────────────────────────── Seats, Existing Enrollment per class
        │
        ▼
BASELINE ENROLLMENT ENTERED (HOI) ──────────────────────── Promoted / Failed counts
        │
        ▼
ADMISSION WINDOW OPENS (FDE controls date OR per-school)
        │
        ▼
DAILY ADMISSIONS ENTERED (HOI) ─ every day ─────────────── Regular + OOSC + P2G + Matric Tech
        │                                                   Morning + Evening (per shift)
        ▼
DATA FLOWS TO ALL DASHBOARDS (real-time)
    ├── HOI Dashboard ──── own school view
    ├── AEO Dashboard ──── sector schools view  
    ├── FDE Dashboard ──── system-wide view
    ├── Director Dashboard ─ read-only system-wide view
    └── Public Portal ───── seat availability + merit lists
        │
        ▼
REPORTS GENERATED (FDE) ────────────────────────────────── Master / Gender / Sector / OOSC / Vacancy
        │
        ▼
POST-ADMISSION MONITORING (HOI / AEO) ──────────────────── Test → Merit → Documents → Confirmed
        │
        ▼
TRANSFERS / CORRECTIONS / REFERRALS (as needed)
        │
        ▼
ADMISSION PERIOD CLOSED (FDE)
```

---

## Summary: Module Quick Reference

| # | Module | Primary User | Purpose |
|---|---|---|---|
| 1 | Authentication | All | Secure login & role-based access |
| 2 | Admin Panel | Admin | System configuration, users, schools |
| 3 | School Profile | HOI | Confirm school identity before setup |
| 4 | Facilities Setup | HOI | Declare programs (Evening, Matric Tech, ECE, Transport, Meals) |
| 5 | Class & Section Setup | HOI | Configure seats, sections, existing enrollment |
| 6 | Baseline Enrollment | HOI | Record promoted/existing students |
| 7 | Daily Admissions | HOI | Enter new students daily (all categories) |
| 8 | Admission Report | HOI | View cumulative school-level report |
| 9 | Monitoring | HOI/AEO/Director | Post-admission student workflow tracking |
| 10 | Student Transfers | HOI/FDE | Move students between FDE schools |
| 11 | Student Referrals | FDE/HOI | Direct student placement management |
| 12 | Merit Lists | HOI/Public | Upload and publish selection lists |
| 13 | Staff Strength | HOI | Record teacher and staff counts |
| 14 | New Construction Rooms | HOI/FDE | Track and allocate new classrooms |
| 15 | Admission Corrections | HOI/FDE | Formal correction request workflow |
| 16 | FDE Reports | FDE/AEO/Director | Analytics: master, gender, sector, OOSC, vacancy |
| 17 | Seat Configuration | FDE | Override school seat figures |
| 18 | Admission Period Control | FDE/Admin | Open/close admissions per school or system-wide |
| 19 | Audit Log | FDE | Full history of all system actions |
| 20 | Public Portal | Public | Parent-facing school search and seat availability |
| 21 | HOI Dashboard | HOI | Real-time school enrollment snapshot |
| 22 | AEO/Director Dashboard | AEO/Director | Sector/system-wide enrollment overview |
| 23 | FDE Dashboard | FDE | System-wide command center with all stats |

---

*Document prepared for Federal Directorate of Education, Islamabad*
*FDE Admission Portal 2026–27 — Confidential*
