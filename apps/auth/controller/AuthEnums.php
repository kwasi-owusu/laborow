<?php

enum AuthEnums: string
{
    case login_page_name            = "do.login.php";
    case secure_form_cors_hash      = "e6d440973080c6863ca49c38320899085f7180a8e5ecde090c66dd155692c61b584e6d2ee0db701ef20bfeeba438676385a80e7897c70fa75187121c731b76a1";
}

enum PasswordSecurity: int
{
    case password_expires_after_days    = 30;
    case password_min_num_of_chars      = 8;
}

enum LaborowHashKeys: string
{
    case password_hash          = "bahrima_kwasi_owusu";
}
