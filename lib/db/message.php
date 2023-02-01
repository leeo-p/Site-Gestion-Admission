<?php
namespace DB;

class Message extends Entity {
    const DIRNAME = "messages";
    const TABLE = [
        "sender" => ["DB\\User"],
        "reciever" => ["DB\\User"],
        "subject" => ["Str"],
        "body" => ["Str"],
        "read" => ["Boolean"],
        "deleted" => ["Boolean"],
        "timestamp" => ["DateTime"],
    ];

    public static function create($id = null) {
        $msg = parent::create(unique_id());
        $msg->timestamp = new \DateTime();
        return $msg;
    }
}
?>
