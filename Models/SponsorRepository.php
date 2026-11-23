<?php
declare(strict_types=1);

require_once __DIR__ . '/Sponsor.php';
require_once __DIR__ . '/EventModuleTransaction.php';

/** Persists sponsors and protects links from events during deletion. */
final class SponsorRepository
{
    private const COLUMNS = 'id, name, logo, website, contact_email, created_at, updated_at';

    public function __construct(private PDO $db)
    {
    }

    /** @return list<array{sponsor: Sponsor, eventsCount: int}> */
    public function listWithEventCounts(): array
    {
        $rows = $this->db->query('
            SELECT s.id, s.name, s.logo, s.website, s.contact_email, s.created_at, s.updated_at,
                   (SELECT COUNT(*) FROM events e WHERE e.sponsor_id = s.id) AS events_count
            FROM sponsors s
            ORDER BY s.name, s.id
        ')->fetchAll();

        return array_map(static fn(array $row): array => [
            'sponsor' => Sponsor::fromArray($row),
            'eventsCount' => (int) $row['events_count'],
        ], $rows);
    }

    /** @return list<array{id: int, name: string}> */
    public function options(): array
    {
        $rows = $this->db->query('SELECT id, name FROM sponsors ORDER BY name, id')->fetchAll();
        return array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
        ], $rows);
    }

    public function find(int $id): ?Sponsor
    {
        $stmt = $this->db->prepare('SELECT ' . self::COLUMNS . ' FROM sponsors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : Sponsor::fromArray($row);
    }

    public function create(Sponsor $sponsor): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO sponsors (name, logo, website, contact_email)
            VALUES (:name, :logo, :website, :contact_email)
        ');
        $stmt->execute($this->parameters($sponsor));
        return (int) $this->db->lastInsertId();
    }

    public function update(Sponsor $sponsor): void
    {
        EventModuleTransaction::run($this->db, function () use ($sponsor): void {
            $this->requireLockedSponsor($sponsor->getId());
            $stmt = $this->db->prepare('
                UPDATE sponsors SET name = :name, logo = :logo, website = :website, contact_email = :contact_email
                WHERE id = :id
            ');
            $stmt->execute($this->parameters($sponsor) + ['id' => $sponsor->getId()]);
        });
    }

    public function delete(int $id): void
    {
        EventModuleTransaction::run($this->db, function () use ($id): void {
            $this->requireLockedSponsor($id);
            $linked = $this->db->prepare('SELECT 1 FROM events WHERE sponsor_id = :id LIMIT 1');
            $linked->execute(['id' => $id]);
            if ($linked->fetchColumn() !== false) {
                throw new DomainException('Unlink this sponsor from its events before deleting it.');
            }

            $stmt = $this->db->prepare('DELETE FROM sponsors WHERE id = :id');
            $stmt->execute(['id' => $id]);
        });
    }

    /** Use the same sponsor row lock when assigning a sponsor to an event. */
    public function requireLockedSponsor(int $id): void
    {
        $stmt = $this->db->prepare('SELECT id FROM sponsors WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() === false) {
            throw new OutOfBoundsException('Sponsor not found.');
        }
    }

    private function parameters(Sponsor $sponsor): array
    {
        return [
            'name' => $sponsor->getName(),
            'logo' => $sponsor->getLogo(),
            'website' => $sponsor->getWebsite(),
            'contact_email' => $sponsor->getContactEmail(),
        ];
    }
}
