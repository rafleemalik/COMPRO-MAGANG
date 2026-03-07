# Navigation Flow Diagram
## Sistem Penerimaan Magang - PT Telkom Indonesia

Diagram ini menunjukkan alur navigasi pengguna melalui aplikasi berdasarkan role mereka.

---

## Navigation Flow - Public (Non-Authenticated)

```mermaid
graph TD
    A[Home Page] --> B[About Page]
    A --> C[Program Page]
    A --> D[Login Page]
    A --> E[Register Page]
    
    D --> F{Login<br/>Success?}
    F -->|Yes| G[Redirect to Dashboard]
    F -->|No| D
    
    E --> H{Register<br/>Success?}
    H -->|Yes| I[Auto Login]
    I --> J[Pre-Acceptance Page]
    H -->|No| E
    
    C --> K[Internship List]
    K --> L[Apply for Internship]
    L --> D
```

---

## Navigation Flow - Peserta (Participant)

```mermaid
graph TD
    A[Login] --> B{Peserta<br/>Dashboard}
    
    B --> C[Pre-Acceptance<br/>Lengkapi Data]
    B --> D[Status Pengajuan]
    B --> E[Penugasan]
    B --> F[Absensi]
    B --> G[Logbook]
    B --> H[Sertifikat]
    B --> I[Profil]
    B --> J[Program Info]
    
    C --> C1[Update Profile]
    C --> C2[Upload Documents]
    C --> C3[Set Dates]
    C --> C4[Complete Application]
    C4 --> D
    
    D --> D1[View Status]
    D --> D2[Download Acceptance Letter]
    D --> D3[Upload Additional Docs]
    D --> D4[Reapply if Rejected]
    
    E --> E1[View Assignments]
    E --> E2[Submit Assignment]
    E --> E3[View Grades]
    E --> E4[View Feedback]
    
    F --> F1[Check In]
    F --> F2[Mark Absent]
    F --> F3[View Attendance History]
    
    G --> G1[View Logbooks]
    G --> G2[Create Logbook]
    G --> G3[Edit Logbook]
    G --> G4[Delete Logbook]
    
    H --> H1[View Certificates]
    H --> H2[Download Certificate]
    
    I --> I1[View Profile]
    I --> I2[Change Password]
    I --> I3[Setup 2FA]
    
    J --> J1[View Program Details]
```

---

## Navigation Flow - Mentor (Pembimbing)

```mermaid
graph TD
    A[Login] --> B{2FA<br/>Setup?}
    B -->|No| C[2FA Setup Page]
    B -->|Yes| D{2FA<br/>Verified?}
    D -->|No| E[2FA Verify Page]
    D -->|Yes| F[Mentor Dashboard]
    C --> F
    E --> F
    
    F --> G[Pengajuan Magang]
    F --> H[Penugasan & Penilaian]
    F --> I[Absensi]
    F --> J[Logbook]
    F --> K[Sertifikat]
    F --> L[Laporan Penilaian]
    F --> M[Profil]
    
    G --> G1[View Pengajuan]
    G --> G2[Accept/Reject]
    G --> G3[Generate Acceptance Letter]
    G --> G4[Preview Letter]
    G --> G5[Send Letter]
    
    H --> H1[View Assignments]
    H --> H2[Create Assignment]
    H --> H3[Edit Assignment]
    H --> H4[Delete Assignment]
    H --> H5[Grade Assignment]
    H --> H6[Set Revision]
    H --> H7[View Submissions]
    
    I --> I1[View Attendance]
    I --> I2[Filter by Date]
    I --> I3[View Participant Attendance]
    
    J --> J1[View Logbooks]
    J --> J2[Filter by Participant]
    J --> J3[View Logbook Details]
    
    K --> K1[View Participants]
    K --> K2[Select Participant]
    K --> K3[Generate Certificate]
    K --> K4[Preview Certificate]
    K --> K5[Send Certificate]
    
    L --> L1[View Reports]
    L --> L2[Filter Reports]
    L --> L3[Upload Assessment Report]
    L --> L4[Download Report]
    L --> L5[Delete Report]
    
    M --> M1[View Profile]
    M --> M2[Change Password]
```

---

## Navigation Flow - Admin

```mermaid
graph TD
    A[Login] --> B[Admin Dashboard]
    
    B --> C[Applications<br/>Pengajuan]
    B --> D[Participants<br/>Peserta]
    B --> E[Divisions<br/>Divisi]
    B --> F[Mentors<br/>Pembimbing]
    B --> G[Fields<br/>Bidang Peminatan]
    B --> H[Reports<br/>Laporan]
    B --> I[Attendance]
    B --> J[Logbook]
    B --> K[Rules]
    
    C --> C1[View Applications]
    C --> C2[Approve Application]
    C --> C3[Reject Application]
    C --> C4[Filter Applications]
    
    D --> D1[View Participants]
    D --> D2[Upload Acceptance Letter]
    D --> D3[Upload Completion Letter]
    D --> D4[Upload Certificate]
    D --> D5[Download Assessment Report]
    D --> D6[Filter Participants]
    
    E --> E1[View Divisions]
    E --> E2[Create Division]
    E --> E3[Edit Division]
    E --> E4[Delete Division]
    E --> E5[Toggle Active Status]
    E --> E6[Manage Direktorat]
    E --> E7[Manage SubDirektorat]
    E --> E8[Manage Divisi]
    
    F --> F1[View Mentors]
    F --> F2[View Mentor Detail]
    F --> F3[Reset Password]
    
    G --> G1[View Fields]
    G --> G2[Create Field]
    G --> G3[Edit Field]
    G --> G4[Delete Field]
    G --> G5[Toggle Active Status]
    
    H --> H1[View Reports]
    H --> H2[Filter Reports]
    H --> H3[Export PDF]
    H --> H4[Export Excel]
    H --> H5[View Classifications]
    
    I --> I1[View All Attendance]
    I --> I2[Filter by Date]
    I --> I3[Filter by Participant]
    
    J --> J1[View All Logbooks]
    J --> J2[Filter by Mentor]
    J --> J3[Filter by Participant]
    
    K --> K1[View Rules]
    K --> K2[Edit Rules]
    K --> K3[Update Rules]
```

---

## Navigation Flow - Complete System Map

```mermaid
graph TB
    subgraph "Public Area"
        A[Home]
        A1[About]
        A2[Program]
        A3[Login]
        A4[Register]
    end
    
    subgraph "Auth Area"
        B[2FA Setup]
        B1[2FA Verify]
    end
    
    subgraph "Peserta Area"
        C[Dashboard Peserta]
        C1[Pre-Acceptance]
        C2[Status]
        C3[Assignments]
        C4[Attendance]
        C5[Logbook]
        C6[Certificates]
        C7[Profile]
    end
    
    subgraph "Mentor Area"
        D[Dashboard Mentor]
        D1[Pengajuan]
        D2[Penugasan]
        D3[Attendance]
        D4[Logbook]
        D5[Sertifikat]
        D6[Laporan]
        D7[Profile]
    end
    
    subgraph "Admin Area"
        E[Dashboard Admin]
        E1[Applications]
        E2[Participants]
        E3[Divisions]
        E4[Mentors]
        E5[Fields]
        E6[Reports]
        E7[Attendance]
        E8[Logbook]
        E9[Rules]
    end
    
    A --> A1
    A --> A2
    A --> A3
    A --> A4
    A3 -->|Login Success| B
    A3 -->|Peserta| C
    A3 -->|Mentor| B
    A3 -->|Admin| E
    A4 -->|Register Success| C1
    
    B --> B1
    B1 -->|Verified| D
    B1 -->|Peserta| C
    
    C --> C1
    C --> C2
    C --> C3
    C --> C4
    C --> C5
    C --> C6
    C --> C7
    
    D --> D1
    D --> D2
    D --> D3
    D --> D4
    D --> D5
    D --> D6
    D --> D7
    
    E --> E1
    E --> E2
    E --> E3
    E --> E4
    E --> E5
    E --> E6
    E --> E7
    E --> E8
    E --> E9
```

---

## Navigation Flow - Authentication & Authorization

```mermaid
graph TD
    A[Access Page] --> B{Authenticated?}
    B -->|No| C[Redirect to Login]
    B -->|Yes| D{Role?}
    
    D -->|Peserta| E{2FA Setup?}
    D -->|Mentor| F{2FA Setup?}
    D -->|Admin| G[Admin Access]
    
    E -->|No| H[Force 2FA Setup]
    E -->|Yes| I{2FA Verified?}
    I -->|No| J[Force 2FA Verify]
    I -->|Yes| K[Peserta Access]
    H --> K
    J --> K
    
    F -->|No| L[Force 2FA Setup]
    F -->|Yes| M{2FA Verified?}
    M -->|No| N[Force 2FA Verify]
    M -->|Yes| O[Mentor Access]
    L --> O
    N --> O
    
    C --> P[Login Form]
    P --> Q[Authenticate]
    Q -->|Success| D
    Q -->|Failed| P
```

---

## Navigation Flow - Page Access Matrix

| Page | Public | Peserta | Mentor | Admin |
|------|--------|---------|--------|-------|
| Home | ✅ | ✅ | ✅ | ✅ |
| About | ✅ | ✅ | ✅ | ✅ |
| Program | ✅ | ✅ | ✅ | ✅ |
| Login | ✅ | ✅ | ✅ | ✅ |
| Register | ✅ | ❌ | ❌ | ❌ |
| Dashboard Peserta | ❌ | ✅ | ❌ | ❌ |
| Dashboard Mentor | ❌ | ❌ | ✅ | ❌ |
| Dashboard Admin | ❌ | ❌ | ❌ | ✅ |
| Pre-Acceptance | ❌ | ✅ | ❌ | ❌ |
| Status | ❌ | ✅ | ❌ | ❌ |
| Assignments | ❌ | ✅ | ✅ | ❌ |
| Attendance | ❌ | ✅ | ✅ | ✅ |
| Logbook | ❌ | ✅ | ✅ | ✅ |
| Certificates | ❌ | ✅ | ✅ | ❌ |
| Pengajuan | ❌ | ❌ | ✅ | ❌ |
| Penugasan | ❌ | ❌ | ✅ | ❌ |
| Applications | ❌ | ❌ | ❌ | ✅ |
| Participants | ❌ | ❌ | ❌ | ✅ |
| Divisions | ❌ | ❌ | ❌ | ✅ |
| Mentors | ❌ | ❌ | ❌ | ✅ |
| Reports | ❌ | ❌ | ✅ | ✅ |

---

**Dibuat**: 2024  
**Versi**: 1.0  
**Sistem**: Penerimaan Magang PT Telkom Indonesia

















