<?php

class User
{
    public int $user_id;
    public string $login;
    private string $password;
    public string $first_name;
    public string $last_name;
    public int $permission;
    public int $creation_date;
    private string $token;

    private function setup($row) {
        $this->first_name = $row->first_name;
        $this->last_name = $row->last_name;
        $this->user_id = $row->user_id;
        $this->login = $row->login;
        $this->password = $row->password;
        $this->permission = $row->permission;
        $this->creation_date = $row->creation_date;
        $this->token = $row->token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function update(mysqli $db, string|null $login, string|null $password, array $obj = []): bool {
        $hash_password = $this->password;
        if ($password) $hash_password=password_hash($password, PASSWORD_BCRYPT);
        $token = $this->token;
        if ($password) $token = User::create_token();

        $first_name = $obj['first_name'] ?? $this->first_name;
        $last_name = $obj['last_name'] ?? $this->last_name;
        $permission = $obj['permission'] ?? $this->permission;
        $login = $login ?? $this->login;

        try {
            $stmt = $db->prepare("update user set login=?, password=?, first_name=?, last_name=?, permission=?, token=? where user_id=?");
            $stmt->bind_param("ssssssi", $login, $hash_password, $first_name, $last_name, $permission, $token, $this->user_id);
            if (!$stmt->execute()) badRequestJson("error", 500);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
        $this->login = $login;
        $this->password = $hash_password;
        $this->token = $token;
        $this->permission = $permission;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        return true;
    }
    public function delete(mysqli $db) {
        return $db->query("delete from user where user_id={$this->user_id}");
    }

    public static function user_by_id(mysqli $db, int $id) {
        $query = "SELECT *, UNIX_TIMESTAMP(creation_date) as creation_date FROM user WHERE user_id = $id LIMIT 1";
        $res = $db->query($query);

        if ($res->num_rows == 0) {
            return false;
        }
        $row = $res->fetch_object();

        $u = new User();
        $u->setup($row);
        return $u;
    }
    public static function user_by_token(mysqli $db, $token) {
        $query = "SELECT *, UNIX_TIMESTAMP(creation_date) as creation_date FROM user WHERE token = '$token' LIMIT 1";
        $res = $db->query($query);

        if ($res->num_rows == 0) {
            return false;
        }
        $row = $res->fetch_object();

        $u = new User();
        $u->setup($row);

        return $u;
    }
    public static function user_by_credentials(mysqli $db, string $login, string $password) {
        $query = "select *, UNIX_TIMESTAMP(creation_date) as creation_date from user where login = '$login'";
        $res = $db->query($query);

        if ($res->num_rows != 1) {
            return false;
        }
        $row = $res->fetch_object();

        if (!password_verify($password, $row->password))
            return false;

        $u = new User();
        $u->setup($row);

        return $u;
    }

    /**
     * @return User[]|false
     */
    public static function user_by_search(mysqli $db, string|null $search, int|null $limit, int|null $offset) {
        $phrase = "%{$search}%";
        $limit = $limit ?? 20;
        $offset = $offset ?? 0;

        $stmt = $db->prepare("select *, UNIX_TIMESTAMP(creation_date) as creation_date from user where first_name like ? or last_name like ? or login like ? or user_id like ? limit ? offset ?");
        $stmt->bind_param("ssssii", $phrase, $phrase, $phrase, $phrase, $limit, $offset);
        if (!$stmt->execute()) return false;
        $res = $stmt->get_result();

        $out = [];

        while ($row = $res->fetch_object()) {
            $u = new User();
            $u->setup($row);
            $out[] = $u;
        }

        return $out;
    }

    public static function create_token(): string {
        return bin2hex(random_bytes(8)) . "." . time();
    }

    public static function insert_new(mysqli $db, string $login, string $password, array $obj): User|false { // {first_name, last_name, permission}
        $hash_password = password_hash($password, PASSWORD_BCRYPT);
        $token = User::create_token();

        $first_name = $obj['first_name'] ?? "";
        $last_name = $obj['last_name'] ?? "";
        $permission = $obj['permission'] ?? 0;

        try {
            $stmt = $db->prepare("insert into user (login, password, token, first_name, last_name, permission) value (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi",
                $login,
                $hash_password,
                $token,
                $first_name,
                $last_name,
                $permission
            );
            if (!$stmt->execute()) return false;
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        return User::user_by_id($db, $db->insert_id);
    }

}