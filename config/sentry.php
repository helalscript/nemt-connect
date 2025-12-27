<?php

return [

    // ----------------------
    // VEHICLE ENUM LISTS
    // ----------------------

    'vehicle_status' => [
        1 => 'Active',
        2 => 'Suspended',
        3 => 'Inactive',
    ],

    'vehicle_type' => [
        1 => 'Livery',
        3 => 'Ambulette',
        6 => 'Ambulance',
    ],

    'license_plate_category' => [
        1 => 'Non-Commercial',
        2 => 'Commercial - Livery (DOT)',
        3 => 'Commercial - TLC',
        4 => 'Commercial - BUS (DOT)',
        5 => 'Commercial - Ambulance',
    ],

    'commercial_license_type' => [
        1 => 'FHV',
        2 => 'Paratransit (PR)',
        3 => 'EMT',
    ],

    // ----------------------
    // STATE LIST (USA)
    // From common.yaml (ISO Codes)
    // ----------------------

    'vehicle_states' => [
        "AL" => "Alabama",
        "AK" => "Alaska",
        "AZ" => "Arizona",
        "AR" => "Arkansas",
        "CA" => "California",
        "CO" => "Colorado",
        "CT" => "Connecticut",
        "DE" => "Delaware",
        "FL" => "Florida",
        "GA" => "Georgia",
        "HI" => "Hawaii",
        "ID" => "Idaho",
        "IL" => "Illinois",
        "IN" => "Indiana",
        "IA" => "Iowa",
        "KS" => "Kansas",
        "KY" => "Kentucky",
        "LA" => "Louisiana",
        "ME" => "Maine",
        "MD" => "Maryland",
        "MA" => "Massachusetts",
        "MI" => "Michigan",
        "MN" => "Minnesota",
        "MS" => "Mississippi",
        "MO" => "Missouri",
        "MT" => "Montana",
        "NE" => "Nebraska",
        "NV" => "Nevada",
        "NH" => "New Hampshire",
        "NJ" => "New Jersey",
        "NM" => "New Mexico",
        "NY" => "New York",
        "NC" => "North Carolina",
        "ND" => "North Dakota",
        "OH" => "Ohio",
        "OK" => "Oklahoma",
        "OR" => "Oregon",
        "PA" => "Pennsylvania",
        "RI" => "Rhode Island",
        "SC" => "South Carolina",
        "SD" => "South Dakota",
        "TN" => "Tennessee",
        "TX" => "Texas",
        "UT" => "Utah",
        "VT" => "Vermont",
        "VA" => "Virginia",
        "WA" => "Washington",
        "WV" => "West Virginia",
        "WI" => "Wisconsin",
        "WY" => "Wyoming",
    ],

    // DRIVER STATUS
    'driver_status' => [
        1 => 'Active',
        2 => 'Not Active',
    ],

    // STATES
    'driver_states' => [
    "AL" => "Alabama",
    "AK" => "Alaska",
    "AZ" => "Arizona",
    "AR" => "Arkansas",
    "CA" => "California",
    "CO" => "Colorado",
    "CT" => "Connecticut",
    "DE" => "Delaware",
    "FL" => "Florida",
    "GA" => "Georgia",
    "HI" => "Hawaii",
    "ID" => "Idaho",
    "IL" => "Illinois",
    "IN" => "Indiana",
    "IA" => "Iowa",
    "KS" => "Kansas",
    "KY" => "Kentucky",
    "LA" => "Louisiana",
    "ME" => "Maine",
    "MD" => "Maryland",
    "MA" => "Massachusetts",
    "MI" => "Michigan",
    "MN" => "Minnesota",
    "MS" => "Mississippi",
    "MO" => "Missouri",
    "MT" => "Montana",
    "NE" => "Nebraska",
    "NV" => "Nevada",
    "NH" => "New Hampshire",
    "NJ" => "New Jersey",
    "NM" => "New Mexico",
    "NY" => "New York",
    "NC" => "North Carolina",
    "ND" => "North Dakota",
    "OH" => "Ohio",
    "OK" => "Oklahoma",
    "OR" => "Oregon",
    "PA" => "Pennsylvania",
    "RI" => "Rhode Island",
    "SC" => "South Carolina",
    "SD" => "South Dakota",
    "TN" => "Tennessee",
    "TX" => "Texas",
    "UT" => "Utah",
    "VT" => "Vermont",
    "VA" => "Virginia",
    "WA" => "Washington",
    "WV" => "West Virginia",
    "WI" => "Wisconsin",
    "WY" => "Wyoming"
],

    // GENDER
    'gender' => [
        'M' => 'Male',
        'F' => 'Female',
        'U' => 'Unknown'
    ],

    // LICENSE CLASS
    'driver_license_class' => [
        'A', 'B', 'C', 'D', 'E',
        'M', 'AM', 'BM', 'CM', 'DM',
        'EM', 'DJ', 'MJ', 'DJMJ'
    ],

    // ENDORSEMENTS
    // 'dmv_endorsements' => [
    //     "H" => "Hazmat",
    //     "N" => "Tank",
    //     "P" => "Passenger",
    //     "S" => "School Bus",
    //     "T" => "Double/Triple Trailer",
    //     "X" => "Tank + Hazmat"
    // ],
    'dmv_endorsements' =>[ 'F', 'G', 'H', 'M', 'N', 'P', 'R', 'S', 'T', 'W', 'X' ],

    // RESTRICTIONS
    // 'dmv_restrictions' => [
    //     "A" => "Corrective Lenses",
    //     "B" => "Outside Mirrors",
    //     "C" => "Daytime Driving Only",
    //     "D" => "Auto Transmission Only",
    //     "E" => "No Manual Transmission CMV",
    //     "F" => "No Air Brakes",
    //     "G" => "No Night Driving",
    //     "K" => "CDL Intrastate Only",
    //     "L" => "No Air Brake",
    //     "M" => "No Class A Passenger Vehicle",
    //     "N" => "No Class A/B Passenger Vehicle",
    //     "O" => "No Tractor Trailer",
    //     "Z" => "No Full Air Brake"
    // ],
    'dmv_restrictions' => [ 'A', 'A1', 'A2', 'A3', 'A4', 'B', 'C', 'D', 'E', 'E1', 'F', 'F1', 'G', 'H', 'I', 'I1', 'I2', 'I3', 'I4', 'K', 'L', 'L1', 'L2', 'M', 'N', 'N1', 'N2', 'O', 'O1', 'O2', 'P', 'P1', 'Q', 'R', 'U', 'V', 'V1', 'W', 'X', 'X1', 'Y', 'Z', 'Z1', '4', '5', '1' ],

    // EXTRA COMMERCIAL LICENSE TYPES
    'extra_commercial_license_types' => [
        1 => 'NYC TLC Paratransit',
        2 => 'NYC TLC For-Hire',
        3 => 'Westchester TLC',
        4 => 'Nassau TLC',
        5 => 'Suffolk TLC',
        6 => 'NYS EMT',
    ],

    // CREDENTIAL STATUS
    'credential_status' => [
        1 => 'Waiting for Approval',
        2 => 'Approved',
        3 => 'Rejected',
    ],
];
