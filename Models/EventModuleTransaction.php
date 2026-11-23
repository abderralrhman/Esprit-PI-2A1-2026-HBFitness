<?php
declare(strict_types=1);

/** Commits or rolls back one Module 4 write that spans several statements. */
final class EventModuleTransaction
{
    public static function run(PDO $db, callable $work): mixed
    {
        $db->beginTransaction();
        try {
            $result = $work();
            $db->commit();
            return $result;
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }
}
