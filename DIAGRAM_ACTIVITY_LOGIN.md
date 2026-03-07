# Activity Diagram - Login Process
## Sistem Penerimaan Magang - PT Telkom Indonesia

Diagram ini menunjukkan alur aktivitas proses login dengan 2FA (Two-Factor Authentication).

---

## Activity Diagram - Login Process (Complete Flow)

```mermaid
flowchart TD
    Start([Start: User Access Login Page]) --> A[Display Login Form]
    A --> B[User Enters Username/Password]
    B --> C{Validate Input<br/>Fields Not Empty?}
    
    C -->|No| D[Show Validation Error]
    D --> A
    
    C -->|Yes| E{Username Format?}
    E -->|6 digits NIK| F[Search User by NIK<br/>Role: Pembimbing]
    E -->|Regular Username| G[Search User by Username]
    
    F --> H{User Found?}
    G --> H
    
    H -->|No| I[Show Error:<br/>Username/Password Salah]
    I --> A
    
    H -->|Yes| J[Verify Password]
    J --> K{Password Valid?}
    
    K -->|No| I
    K -->|Yes| L[Login User<br/>Create Session]
    
    L --> M{User Role?}
    
    M -->|Admin| N[Check 2FA Requirement]
    M -->|Peserta| N
    M -->|Pembimbing| N
    
    N --> O{2FA Required?}
    
    O -->|No| P1[Redirect to Dashboard]
    
    O -->|Yes| Q{2FA Already Setup?}
    
    Q -->|No| R[Redirect to 2FA Setup Page]
    R --> S[Display QR Code]
    S --> T[User Scans QR Code<br/>with Authenticator App]
    T --> U[User Enters 6-digit Code]
    U --> V{Code Valid?}
    
    V -->|No| W[Show Error:<br/>Invalid Code]
    W --> U
    
    V -->|Yes| X[Mark 2FA as Verified<br/>Save to Database]
    X --> Y[Redirect to Dashboard]
    
    Q -->|Yes| Z{2FA Verified<br/>in Current Session?}
    
    Z -->|Yes| P1
    Z -->|No| AA[Redirect to 2FA Verify Page]
    
    AA --> AB[Display 2FA Code Input]
    AB --> AC[User Enters 6-digit Code]
    AC --> AD{Code Valid?}
    
    AD -->|No| AE[Show Error:<br/>Invalid Code]
    AE --> AC
    
    AD -->|Yes| AF[Set Session Flag:<br/>2fa_verified = true]
    AF --> AG[Redirect to Dashboard]
    
    P1 --> AH{Determine Dashboard<br/>by Role}
    Y --> AH
    AG --> AH
    
    AH -->|Admin| AI[Admin Dashboard]
    AH -->|Peserta| AJ[Peserta Dashboard]
    AH -->|Pembimbing| AK[Mentor Dashboard]
    
    AI --> End([End: User Logged In])
    AJ --> End
    AK --> End
```

---

## Activity Diagram - 2FA Setup Process

```mermaid
flowchart TD
    Start([Start: User Redirected<br/>to 2FA Setup]) --> A{User Authenticated?}
    
    A -->|No| B[Redirect to Login]
    B --> End1([End])
    
    A -->|Yes| C[Check if 2FA Secret Exists]
    C --> D{Secret Exists?}
    
    D -->|No| E[Generate 2FA Secret<br/>Save to Database]
    D -->|Yes| F[Use Existing Secret]
    
    E --> G[Generate QR Code URL]
    F --> G
    
    G --> H[Display QR Code<br/>Display Secret Key]
    H --> I[User Scans QR Code<br/>with Authenticator App]
    
    I --> J[User Enters Verification Code]
    J --> K{Code Valid?}
    
    K -->|No| L[Show Error:<br/>Invalid Code]
    L --> J
    
    K -->|Yes| M[Mark 2FA as Verified<br/>Set two_factor_verified_at]
    M --> N[Show Success Message]
    N --> O[Redirect to Dashboard]
    
    O --> End2([End: 2FA Enabled])
```

---

## Activity Diagram - 2FA Verification Process

```mermaid
flowchart TD
    Start([Start: User on<br/>2FA Verify Page]) --> A{User Authenticated?}
    
    A -->|No| B[Redirect to Login]
    B --> End1([End])
    
    A -->|Yes| C{2FA Required<br/>for User Role?}
    
    C -->|No| D[Redirect to Dashboard]
    D --> End2([End])
    
    C -->|Yes| E{2FA Setup?}
    
    E -->|No| F[Redirect to 2FA Setup]
    F --> End3([End])
    
    E -->|Yes| G[Display 2FA Code Input Form]
    G --> H[User Enters 6-digit Code]
    
    H --> I{Code Format Valid?<br/>6 digits numeric}
    
    I -->|No| J[Show Format Error]
    J --> H
    
    I -->|Yes| K[Verify Code with<br/>Google2FA Library]
    
    K --> L{Code Valid?}
    
    L -->|No| M[Show Error:<br/>Invalid Code]
    M --> N{Attempt Count < 5?}
    
    N -->|Yes| H
    N -->|No| O[Too Many Attempts<br/>Logout User]
    O --> P[Redirect to Login]
    P --> End1
    
    L -->|Yes| Q[Set Session Flag:<br/>2fa_verified = true]
    Q --> R[Redirect to Intended URL<br/>or Dashboard]
    
    R --> End4([End: 2FA Verified<br/>User Logged In])
```

---

## Activity Diagram - Login Process (Simplified - Peserta)

```mermaid
flowchart TD
    Start([Start]) --> A[Access Login Page]
    A --> B[Enter Username & Password]
    B --> C[Submit Form]
    C --> D{Input Valid?}
    
    D -->|No| E[Show Validation Errors]
    E --> B
    
    D -->|Yes| F[Authenticate Credentials]
    F --> G{Valid?}
    
    G -->|No| H[Show Error Message]
    H --> B
    
    G -->|Yes| I[Check 2FA Status]
    I --> J{2FA Enabled?}
    
    J -->|No| K[Setup 2FA]
    K --> L[Verify 2FA Code]
    L --> M{Valid?}
    M -->|No| L
    M -->|Yes| N[Login Success]
    
    J -->|Yes| O{2FA Verified?}
    O -->|No| P[Verify 2FA Code]
    P --> Q{Valid?}
    Q -->|No| P
    Q -->|Yes| N
    
    O -->|Yes| N
    
    N --> R[Redirect to Dashboard]
    R --> End([End])
```

---

## Activity Diagram - Login Process (Simplified - Mentor)

```mermaid
flowchart TD
    Start([Start]) --> A[Access Login Page]
    A --> B{Login Method?}
    
    B -->|NIK 6 digits| C[Search by NIK<br/>Role: Pembimbing]
    B -->|Username| D[Search by Username]
    
    C --> E[Enter Password]
    D --> E
    
    E --> F[Submit]
    F --> G{Password Valid?}
    
    G -->|No| H[Show Error]
    H --> E
    
    G -->|Yes| I{2FA Setup?}
    
    I -->|No| J[Force 2FA Setup]
    J --> K[Scan QR Code]
    K --> L[Enter Verification Code]
    L --> M{Valid?}
    M -->|No| L
    M -->|Yes| N[2FA Enabled]
    
    I -->|Yes| O{2FA Verified<br/>in Session?}
    
    O -->|No| P[Verify 2FA Code]
    P --> Q{Valid?}
    Q -->|No| P
    Q -->|Yes| R[Set Session Flag]
    
    O -->|Yes| S[Login Success]
    N --> S
    R --> S
    
    S --> T[Redirect to Mentor Dashboard]
    T --> End([End])
```

---

## Activity Diagram - Logout Process

```mermaid
flowchart TD
    Start([Start: User Clicks Logout]) --> A[Send Logout Request]
    A --> B[Clear User Session]
    B --> C[Invalidate Session Token]
    C --> D[Clear 2FA Verification Flag]
    D --> E[Regenerate CSRF Token]
    E --> F[Logout User from Auth]
    F --> G[Redirect to Home Page]
    G --> End([End: User Logged Out])
```

---

## Activity Diagram - Password Change Process

```mermaid
flowchart TD
    Start([Start: User Accesses<br/>Change Password]) --> A{User Authenticated?}
    
    A -->|No| B[Redirect to Login]
    B --> End1([End])
    
    A -->|Yes| C[Display Change Password Form]
    C --> D[User Enters Current Password]
    D --> E[User Enters New Password]
    E --> F[User Confirms New Password]
    F --> G[Submit Form]
    
    G --> H{Input Valid?<br/>All fields filled}
    
    H -->|No| I[Show Validation Errors]
    I --> C
    
    H -->|Yes| J{Password Match<br/>Confirmation?}
    
    J -->|No| K[Show Error:<br/>Passwords do not match]
    K --> C
    
    J -->|Yes| L{New Password Different<br/>from Current?}
    
    L -->|No| M[Show Error:<br/>New password must be different]
    M --> C
    
    L -->|Yes| N{Current Password<br/>Correct?}
    
    N -->|No| O[Show Error:<br/>Current password incorrect]
    O --> C
    
    N -->|Yes| P{New Password<br/>Meets Requirements?<br/>Min 6 characters}
    
    P -->|No| Q[Show Error:<br/>Password requirements not met]
    Q --> C
    
    P -->|Yes| R[Hash New Password<br/>with bcrypt]
    R --> S[Update Password in Database]
    S --> T[Show Success Message]
    T --> U[Redirect Back to Profile]
    
    U --> End2([End: Password Changed])
```

---

## Activity Diagram - Registration Process

```mermaid
flowchart TD
    Start([Start: User Accesses<br/>Register Page]) --> A[Display Registration Form]
    A --> B[User Fills Form:<br/>Name, Email, Password, etc.]
    B --> C[User Submits Form]
    
    C --> D{Input Validation}
    D -->|Invalid| E[Show Validation Errors]
    E --> B
    
    D -->|Valid| F{Email Already Exists?}
    
    F -->|Yes| G[Show Error:<br/>Email already registered]
    G --> B
    
    F -->|No| H[Hash Password]
    H --> I[Create User Record<br/>Role: Peserta]
    I --> J[Generate 2FA Secret<br/>for User]
    J --> K[Create Internship Application<br/>Status: Pending]
    
    K --> L[Auto Login User]
    L --> M[Redirect to Pre-Acceptance Page]
    M --> N[Show Success Message:<br/>Complete Your Profile]
    
    N --> End([End: User Registered<br/>and Logged In])
```

---

## Key Decision Points

### Decision Points dalam Login Flow:

1. **Input Validation**: Apakah username dan password sudah diisi?
2. **Username Format**: Apakah format NIK (6 digits) atau username biasa?
3. **User Found**: Apakah user ditemukan di database?
4. **Password Valid**: Apakah password yang dimasukkan benar?
5. **User Role**: Admin, Peserta, atau Pembimbing?
6. **2FA Required**: Apakah role user memerlukan 2FA?
7. **2FA Setup**: Apakah 2FA sudah di-setup?
8. **2FA Verified**: Apakah 2FA sudah diverifikasi di session?
9. **Code Valid**: Apakah kode 2FA yang dimasukkan valid?

---

**Dibuat**: 2024  
**Versi**: 1.0  
**Sistem**: Penerimaan Magang PT Telkom Indonesia

















