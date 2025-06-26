<?php 

class ProfileContact
{
    public string $email;
    public string $phone;
    public string $password;

    public function __construct()
    {
        $this->email = 'alice@example.com';
        $this->phone = '+55 11 91234-5678';
        $this->password = 'qwerty2024';
    }
}

class Profile
{
    public string $name;
    public ProfileContact $contact;
    public array $notes;

    // Referência circular opcional
    public ?object $circular_ref = null;

    public function __construct()
    {
        $this->name = 'Alice';
        $this->contact = new ProfileContact();
        $this->notes = ['This user password is qwerty2024 and CPF is 12345678900.'];
    }
}

class Credentials
{
    public string $password;
    public string $cpf;
    public string $token;

    public function __construct()
    {
        $this->password = 'mypassword';
        $this->cpf = '12345678900';
        $this->token = 'abcde12345token';
    }
}

class Session
{
    public string $session_id;
    public string $ip;
    public string $details;

    // Referência circular opcional
    public ?object $parent_block = null;

    public function __construct($id, $ip, $details)
    {
        $this->session_id = $id;
        $this->ip = $ip;
        $this->details = $details;
    }
}

class UserBlock
{
    public string $userText;
    public Credentials $credentials;
    public Profile $profile;
    /** @var Session[] */
    public array $sessions;
    public bool $is_active;
    public int $login_count;

    // Referência circular opcional
    public ?object $self_ref = null;

    public function __construct()
    {
        $this->userText = "User password: password1234 CPF: 98765432100 Channel: password.";
        $this->credentials = new Credentials();
        $this->profile = new Profile();
        $this->sessions = [
            new Session(
                "app_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
                "192.168.1.100",
                "Token: abcde12345token"
            ),
            new Session(
                "app_z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4",
                "192.168.1.101",
                "Password for access: password123!"
            ),
        ];
        $this->is_active = true;
        $this->login_count = 7;
    }
}

// Criação do objeto raiz
$objectData = (object) [
    'summary' => "Sensitive data array: Password: 12345678900 CPF: 12345678900 Channel: password.",
    'block'   => new UserBlock(),
];

// Referência circular principal: UserBlock aponta para ele mesmo
$objectData->block->self_ref = $objectData->block;

// Referência circular profunda: Profile aponta para UserBlock (de volta ao topo)
$objectData->block->profile->circular_ref = $objectData->block;

// Referência circular em Session: session[0] aponta para UserBlock
$objectData->block->sessions[0]->parent_block = $objectData->block;


return $objectData;