<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/SessionRepository.php';

final class SessionController
{
    public function __construct(private SessionRepository $sessions) {}

    public function validate(array $data): array
    {
        $errors = [];
        $title = trim((string)($data['title'] ?? ''));
        $mode = (string)($data['mode'] ?? '');
        $startAt = trim((string)($data['start_at'] ?? ''));
        $duration = (int)($data['duration_min'] ?? 0);
        $capacity = (int)($data['max_participants'] ?? 0);
        $location = trim((string)($data['location'] ?? ''));
        $coachId = (int)($data['coach_id'] ?? 0);

        if ($title === '' || mb_strlen($title) > 120) $errors['title'] = 'Enter a session title (1–120 characters).';
        if (!in_array($mode, ['in-person', 'online'], true)) $errors['mode'] = 'Choose a valid session mode.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $startAt)) {
            $errors['start_at'] = 'Choose a valid start date and time.';
        } elseif (strtotime(str_replace('T', ' ', $startAt)) <= time()) {
            $errors['start_at'] = 'The session must start in the future.';
        }
        if ($duration < 15 || $duration > 300) $errors['duration_min'] = 'Duration must be between 15 and 300 minutes.';
        if ($capacity < 1 || $capacity > 100) $errors['max_participants'] = 'Capacity must be between 1 and 100.';
        if ($location === '' || mb_strlen($location) > 150) $errors['location'] = 'Enter a location or meeting link (1–150 characters).';
        if ($coachId < 1) $errors['coach_id'] = 'Choose a coach.';

        return $errors;
    }

    public function normalized(array $data): array
    {
        return [
            'title' => trim((string)($data['title'] ?? '')),
            'mode' => (string)($data['mode'] ?? 'in-person'),
            'start_at' => str_replace('T', ' ', trim((string)($data['start_at'] ?? ''))) . ':00',
            'duration_min' => (int)($data['duration_min'] ?? 0),
            'max_participants' => (int)($data['max_participants'] ?? 0),
            'location' => trim((string)($data['location'] ?? '')),
            'coach_id' => (int)($data['coach_id'] ?? 0),
        ];
    }

    public function updatableFor(User $user, TrainingSession $session): bool
    {
        return $user->getRoleName() === 'admin' || $session->getCoachId() === $user->getId();
    }
}
