<?php

namespace App\Enums;

/**
 * NFEMIS StudentAdmissionRegister.Status codes used by the FDE integration.
 *
 * Protocol agreed between FDE Portal and NFEMIS team:
 *
 *  20 → Approved    : NFEMIS sets this when a child is ready for FDE pickup
 *  21 → Received    : FDE writes this when it imports the referral (ProcessNfemisReferralsJob)
 *  22 → Enrolled    : FDE writes this when admission is confirmed (SyncStatusToNfemisJob)
 *  23 → Rejected    : FDE writes this when admission is rejected  (SyncStatusToNfemisJob)
 */
class NfemisStatus
{
    const APPROVED = 20;   // Set by NFEMIS — child is ready for FDE
    const RECEIVED = 21;   // Set by FDE    — referral picked up, admission created
    const ENROLLED = 22;   // Set by FDE    — student confirmed/enrolled
    const REJECTED = 23;   // Set by FDE    — student rejected
}
