<table>
    <tr>
        <td colspan="12"><strong>Staff Strength Register — {{ $register->institution->name }}</strong></td>
    </tr>
    <tr>
        <td>EMIS Code</td><td>{{ $register->institution->code }}</td>
        <td>Type</td><td>{{ $register->institution->type }}</td>
        <td>Sector</td><td>{{ $register->institution->sector->name ?? '' }}</td>
        <td>Academic Year</td><td>{{ $register->academicYear->name }}</td>
        <td>Status</td><td>{{ ucfirst($register->status) }}</td>
        <td></td><td></td>
    </tr>
    <tr></tr>

    {{-- Section A --}}
    <tr>
        <td><strong>Section A — Teaching &amp; Academic Posts</strong></td>
    </tr>
    <tr>
        <td><strong>Post</strong></td>
        <td><strong>Sanctioned</strong></td>
        <td><strong>Filled</strong></td>
        <td><strong>Vacant</strong></td>
        <td><strong>Sacked</strong></td>
        <td><strong>DW-IN</strong></td>
        <td><strong>DW-OUT</strong></td>
        <td><strong>Study Leave</strong></td>
        <td><strong>Dep-IN</strong></td>
        <td><strong>Dep-OUT</strong></td>
        <td><strong>Temp-IN</strong></td>
        <td><strong>Temp-OUT</strong></td>
    </tr>
    @foreach($teachingEntries as $entry)
    <tr>
        <td>{{ $entry->postType->name }}</td>
        <td>{{ $entry->sanctioned_posts }}</td>
        <td>{{ $entry->filled_posts }}</td>
        <td>{{ $entry->vacant_posts }}</td>
        <td>{{ $entry->sacked_employees }}</td>
        <td>{{ $entry->daily_wagers_in }}</td>
        <td>{{ $entry->daily_wagers_out }}</td>
        <td>{{ $entry->study_leave }}</td>
        <td>{{ $entry->deputationist_in }}</td>
        <td>{{ $entry->deputationist_out }}</td>
        <td>{{ $entry->temporary_in }}</td>
        <td>{{ $entry->temporary_out }}</td>
    </tr>
    @endforeach

    <tr></tr>

    {{-- Section B --}}
    <tr>
        <td><strong>Section B — Program Posts</strong></td>
    </tr>
    <tr>
        <td><strong>Program</strong></td>
        <td><strong>Number of Posts</strong></td>
    </tr>
    @foreach($programEntries as $entry)
    <tr>
        <td>{{ $entry->postType->name }}</td>
        <td>{{ $entry->number_of_posts }}</td>
    </tr>
    @endforeach

    <tr></tr>
    <tr>
        <td><strong>Total Staff Physically Present on Duty</strong></td>
        <td><strong>{{ $register->totalPresentOnDuty() }}</strong></td>
    </tr>
</table>
