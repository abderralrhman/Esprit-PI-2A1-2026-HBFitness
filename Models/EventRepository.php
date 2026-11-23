<?php
declare(strict_types=1);

require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/SponsorRepository.php';

/** Persists events and supplies their sponsor and attendance data to the controllers. */
final class EventRepository
{
    private const EVENT_COLUMNS = 'id, title, description, location, event_date, capacity, sponsor_id, created_at, updated_at';
    private const EVENT_DETAILS = '
        SELECT e.id, e.title, e.description, e.location, e.event_date, e.capacity,
               e.sponsor_id, e.created_at, e.updated_at, s.name AS sponsor_name,
               s.logo AS sponsor_logo, s.website AS sponsor_website,
               (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS registrations_count
        FROM events e
        LEFT JOIN sponsors s ON s.id = e.sponsor_id
    ';

    public function __construct(private PDO $db, private SponsorRepository $sponsors)
    {
    }

    /** @return list<array{event: Event, sponsorName: ?string, sponsorLogo: ?string, sponsorWebsite: ?string, registrationsCount: int}> */
    public function listWithDetails(bool $newestFirst = false): array
    {
        $ordering = $newestFirst ? ' ORDER BY e.event_date DESC, e.id DESC' : ' ORDER BY e.event_date, e.id';
        $rows = $this->db->query(self::EVENT_DETAILS . $ordering)->fetchAll();
        return array_map([$this, 'details'], $rows);
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(self::EVENT_DETAILS . ' WHERE e.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $this->details($row);
    }

    public function create(Event $event): int
    {
        return EventModuleTransaction::run($this->db, function () use ($event): int {
            $this->lockSponsor($event);
            $stmt = $this->db->prepare('
                INSERT INTO events (title, description, location, event_date, capacity, sponsor_id)
                VALUES (:title, :description, :location, :event_date, :capacity, :sponsor_id)
            ');
            $stmt->execute($this->parameters($event));
            return (int) $this->db->lastInsertId();
        });
    }

    public function update(Event $event): void
    {
        $this->withLockedEvent($event->getId(), function () use ($event): void {
            $this->lockSponsor($event);
            if ($event->getCapacity() < $this->countRegistrations($event->getId())) {
                throw new DomainException('Capacity cannot be lower than the number of registrations.');
            }

            $stmt = $this->db->prepare('
                UPDATE events SET title = :title, description = :description, location = :location,
                    event_date = :event_date, capacity = :capacity, sponsor_id = :sponsor_id
                WHERE id = :id
            ');
            $stmt->execute($this->parameters($event) + ['id' => $event->getId()]);
        });
    }

    public function delete(int $id): void
    {
        $this->withLockedEvent($id, function () use ($id): void {
            if ($this->countRegistrations($id) > 0) {
                throw new DomainException('An event with registrations cannot be deleted.');
            }

            $stmt = $this->db->prepare('DELETE FROM events WHERE id = :id');
            $stmt->execute(['id' => $id]);
        });
    }

    /** Lock the event before any registration, capacity, or deletion checks. */
    public function withLockedEvent(int $id, callable $work): mixed
    {
        return EventModuleTransaction::run($this->db, function () use ($id, $work): mixed {
            $stmt = $this->db->prepare('SELECT ' . self::EVENT_COLUMNS . ' FROM events WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if ($row === false) {
                throw new OutOfBoundsException('Event not found.');
            }

            return $work(Event::fromArray($row));
        });
    }

    public function countRegistrations(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    private function lockSponsor(Event $event): void
    {
        $sponsorId = $event->getSponsorId();
        if ($sponsorId !== null) {
            try {
                $this->sponsors->requireLockedSponsor($sponsorId);
            } catch (OutOfBoundsException $error) {
                throw new DomainException('Select an existing sponsor or leave the sponsor blank.', 0, $error);
            }
        }
    }

    private function parameters(Event $event): array
    {
        return [
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'event_date' => $event->getEventDate(),
            'capacity' => $event->getCapacity(),
            'sponsor_id' => $event->getSponsorId(),
        ];
    }

    private function details(array $row): array
    {
        return [
            'event' => Event::fromArray($row),
            'sponsorName' => $row['sponsor_name'],
            'sponsorLogo' => $row['sponsor_logo'],
            'sponsorWebsite' => $row['sponsor_website'],
            'registrationsCount' => (int) $row['registrations_count'],
        ];
    }
}
