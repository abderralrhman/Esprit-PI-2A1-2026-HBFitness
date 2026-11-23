<?php
declare(strict_types=1);

require_once __DIR__ . '/EventModuleRequest.php';
require_once __DIR__ . '/../Models/EventRegistrationRepository.php';

/** Handles public events, event administration, and authenticated registrations. */
final class EventController
{
    public function __construct(
        private AuthController $auth,
        private SponsorRepository $sponsors,
        private EventRepository $events,
        private EventRegistrationRepository $registrations
    ) {
    }

    public function handle(string $action, ?string $flashMessage, string $flashType): void
    {
        $currentPage = $action;
        if (str_starts_with($action, 'admin_')) {
            $currentUser = $this->auth->requireRole('admin');
        } elseif ($action === 'event_register' || $action === 'event_cancel') {
            EventModuleRequest::allow('POST');
            $currentUser = $this->auth->requireAuth();
        } else {
            EventModuleRequest::allow('GET');
            $currentUser = $this->auth->getCurrentUser();
        }

        try {
            switch ($action) {
                case 'events':
                    $events = $this->events->listWithDetails();
                    require __DIR__ . '/../Views/Frontoffice/events/index.php';
                    return;

                case 'event':
                    $details = $this->find(EventModuleRequest::id());
                    $event = $details['event'];
                    $registered = $currentUser !== null
                        && $this->registrations->hasRegistration($event->getId(), $currentUser->getId());
                    $csrfToken = $currentUser !== null ? $this->auth->getCsrfToken() : '';
                    $logo = $details['sponsorLogo'];
                    $website = $details['sponsorWebsite'];
                    $sponsorLogo = $logo !== null && EventModuleRequest::validLogo($logo) ? $logo : null;
                    $sponsorWebsite = $website !== null && EventModuleRequest::validWebsite($website) ? $website : null;
                    require __DIR__ . '/../Views/Frontoffice/events/show.php';
                    return;

                case 'event_register':
                case 'event_cancel':
                    EventModuleRequest::requireCsrf($this->auth, $currentUser);
                    $this->changeRegistration($action, EventModuleRequest::id(), $currentUser->getId());

                case 'admin_events':
                    EventModuleRequest::allow('GET');
                    $events = $this->events->listWithDetails(true);
                    $csrfToken = $this->auth->getCsrfToken();
                    require __DIR__ . '/../Views/Backoffice/events/index.php';
                    return;

                case 'admin_event_registrations':
                    EventModuleRequest::allow('GET');
                    $event = $this->find(EventModuleRequest::id())['event'];
                    $participants = $this->registrations->participants($event->getId());
                    require __DIR__ . '/../Views/Backoffice/events/registrations.php';
                    return;

                case 'admin_delete_event':
                    EventModuleRequest::allow('POST');
                    EventModuleRequest::requireCsrf($this->auth, $currentUser);
                    $this->delete(EventModuleRequest::id());

                case 'admin_create_event':
                case 'admin_update_event':
                    $method = EventModuleRequest::allow('GET', 'POST');
                    $event = $action === 'admin_create_event' ? new Event() : $this->find(EventModuleRequest::id())['event'];
                    $errors = [];
                    $values = [
                        'title' => $event->getTitle(),
                        'description' => $event->getDescription(),
                        'location' => $event->getLocation(),
                        'event_date' => str_replace(' ', 'T', $event->getEventDate()),
                        'capacity' => $event->getId() !== 0 ? (string) $event->getCapacity() : '',
                        'sponsor_id' => (string) ($event->getSponsorId() ?? ''),
                    ];
                    if ($method === 'POST') {
                        EventModuleRequest::requireCsrf($this->auth, $currentUser);
                        $values = EventModuleRequest::values($values, $errors);
                        $this->save($event->getId(), $values, $errors);
                        http_response_code(isset($errors['_form']) ? 409 : 422);
                    }
                    $sponsors = $this->sponsors->options();
                    $csrfToken = $this->auth->getCsrfToken();
                    require __DIR__ . '/../Views/Backoffice/events/form.php';
                    return;
            }
        } catch (OutOfBoundsException $error) {
            EventModuleRequest::fail(404, $error->getMessage(), $currentUser);
        }
    }

    private function find(int $id): array
    {
        return $this->events->findWithDetails($id) ?? throw new OutOfBoundsException('Event not found.');
    }

    private function save(int $id, array $values, array &$errors): void
    {
        foreach (['title' => 'Title', 'location' => 'Location'] as $field => $label) {
            if ($values[$field] === '' || mb_strlen($values[$field], 'UTF-8') > 180) {
                $errors[$field] ??= $label . ' is required and must have at most 180 characters.';
            }
        }
        if ($values['description'] === '' || strlen($values['description']) > 65535) {
            $errors['description'] ??= 'Enter a description of up to 65,535 bytes.';
        }

        $date = $this->parseEventDate($values['event_date']);
        if ($date === null) {
            $errors['event_date'] ??= 'Enter a valid event date and time in Tunis local time.';
        }
        $capacity = EventModuleRequest::unsignedInt($values['capacity']);
        if ($capacity === null) {
            $errors['capacity'] ??= 'Enter a whole number from 0 to 4294967295.';
        }
        $sponsorId = EventModuleRequest::unsignedInt($values['sponsor_id']);
        if ($values['sponsor_id'] !== '' && ($sponsorId === null || $sponsorId === 0)) {
            $errors['sponsor_id'] ??= 'Select an existing sponsor or leave the sponsor blank.';
        }
        if ($errors !== []) {
            return;
        }

        $updated = new Event(
            id: $id, title: $values['title'], description: $values['description'], location: $values['location'],
            eventDate: $date, capacity: $capacity, sponsorId: $sponsorId
        );
        try {
            if ($id === 0) {
                $this->events->create($updated);
                AuthController::flash('Event created.');
            } else {
                $this->events->update($updated);
                AuthController::flash('Event updated.');
            }
        } catch (DomainException $error) {
            $errors['_form'] = $error->getMessage();
            return;
        }
        EventModuleRequest::redirect('admin_events');
    }

    private function parseEventDate(string $value): ?string
    {
        if (!preg_match('/\A[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}(?::[0-9]{2})?\z/', $value)) {
            return null;
        }
        $normalized = strlen($value) === 16 ? $value . ':00' : $value;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s', $normalized, new DateTimeZone(Event::TIMEZONE));
        return $date !== false && $date->format('Y-m-d\TH:i:s') === $normalized ? $date->format('Y-m-d H:i:s') : null;
    }

    private function changeRegistration(string $action, int $eventId, int $userId): never
    {
        try {
            if ($action === 'event_register') {
                $this->registrations->register($eventId, $userId);
                AuthController::flash('You are registered for this event.');
            } else {
                $this->registrations->cancel($eventId, $userId);
                AuthController::flash('Your registration was cancelled.');
            }
        } catch (DomainException $error) {
            AuthController::flash($error->getMessage(), 'error');
        }
        EventModuleRequest::redirect('event', $eventId);
    }

    private function delete(int $id): never
    {
        try {
            $this->events->delete($id);
            AuthController::flash('Event deleted.');
        } catch (DomainException $error) {
            AuthController::flash($error->getMessage(), 'error');
        }
        EventModuleRequest::redirect('admin_events');
    }
}
