<?php

class Payment
{
    public int $payment_id;
    public int $provider_id;
    public int $order_id;
    public string $code;

    public function delete(mysqli $db): bool {
        return $db->query("delete from payment where payment_id={$this->payment_id}");
    }
    public function fulfill(mysqli $db): bool {
        $order = Order::fetch_by_id($db, $this->order_id);
        if ($order && $order->status==OrderStatus::pending)
            $order->update_status($db, OrderStatus::paid);
        return $this>$this->delete($db);
    }
    public function fetch_provider(mysqli $db): Provider|false {
        return Provider::fetch_by_id($db, $this->provider_id);
    }

    public static function generate_code(): string {
        return bin2hex(random_bytes(3));
    }

    public static function fetch_by_id(mysqli $db, $id): Payment|false {
        $res = $db->query("select * from payment where payment_id={$id}");
        if (!$res || $res->num_rows != 1) return false;
        return $res->fetch_object("Payment");
    }
    public static function fetch_by_order(mysqli $db, $order_id): Payment|false {
        $res = $db->query("select * from payment where order_id={$order_id}");
        if (!$res || $res->num_rows < 1) return false;
        return $res->fetch_object("Payment");
    }

    public static function insert_new(mysqli $db, $provider_id, $order_id): Payment|false {
        $code = Payment::generate_code();
        $stmt = $db->prepare("insert into payment (provider_id, order_id, code) value (?, ?, ?)");
        $stmt->bind_param("iis", $provider_id, $order_id, $code);
        if (!$stmt->execute()) return false;
        return Payment::fetch_by_id($db, $db->insert_id);
    }
}