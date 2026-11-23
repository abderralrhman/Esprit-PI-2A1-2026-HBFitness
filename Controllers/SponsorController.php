<?php
declare(strict_types=1);

require_once __DIR__ . '/EventModuleRequest.php';
require_once __DIR__ . '/../Models/SponsorRepository.php';

/** Handles the Backoffice sponsor list, editor, and delete action. */
final class SponsorController
{
    public function __construct(private AuthController $auth, private SponsorRepository $sponsors)
    {
    }

    public function handle(string $action, ?string $flashMessage, string $flashType): void
    {
        $currentUser = $this->auth->requireRole('admin');
        $currentPage = $action;
        $csrfToken = $this->auth->getCsrfToken();

        try {
            switch ($action) {
                case 'admin_sponsors':
                    EventModuleRequest::allow('GET');
                    $sponsors = $this->sponsors->listWithEventCounts();
                    require __DIR__ . '/../Views/Backoffice/sponsors/index.php';
                    return;

                case 'admin_delete_sponsor':
                    EventModuleRequest::allow('POST');
                    EventModuleRequest::requireCsrf($this->auth, $currentUser);
                    $this->delete(EventModuleRequest::id());

                case 'admin_create_sponsor':
                case 'admin_update_sponsor':
                    $method = EventModuleRequest::allow('GET', 'POST');
                    $sponsor = $action === 'admin_create_sponsor' ? new Sponsor() : $this->find(EventModuleRequest::id());
                    $errors = [];
                    $values = [
                        'name' => $sponsor->getName(),
                        'logo' => $sponsor->getLogo() ?? '',
                        'website' => $sponsor->getWebsite() ?? '',
                        'contact_email' => $sponsor->getContactEmail() ?? '',
                    ];
                    if ($method === 'POST') {
                        EventModuleRequest::requireCsrf($this->auth, $currentUser);
                        $values = EventModuleRequest::values($values, $errors);
                        $errors += $this->validate($values);
                        if ($errors === []) {
                            $this->save($sponsor->getId(), $values);
                        }
                        http_response_code(422);
                    }
                    require __DIR__ . '/../Views/Backoffice/sponsors/form.php';
                    return;
            }
        } catch (OutOfBoundsException $error) {
            EventModuleRequest::fail(404, $error->getMessage(), $currentUser);
        }
    }

    private function find(int $id): Sponsor
    {
        return $this->sponsors->find($id) ?? throw new OutOfBoundsException('Sponsor not found.');
    }

    private function validate(array $values): array
    {
        $errors = [];
        if ($values['name'] === '' || mb_strlen($values['name'], 'UTF-8') > 120) {
            $errors['name'] = 'Enter a sponsor name of up to 120 characters.';
        }
        if ($values['logo'] !== '' && (strlen($values['logo']) > 500 || !EventModuleRequest::validLogo($values['logo']))) {
            $errors['logo'] = 'Choose an existing image under Views/assets/images/.';
        }
        if ($values['website'] !== '' && (strlen($values['website']) > 500 || !EventModuleRequest::validWebsite($values['website']))) {
            $errors['website'] = 'Enter an HTTP or HTTPS URL of up to 500 characters.';
        }
        if ($values['contact_email'] !== '' && (strlen($values['contact_email']) > 150
            || filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL) === false)) {
            $errors['contact_email'] = 'Enter a valid email address of up to 150 characters.';
        }
        return $errors;
    }

    private function save(int $id, array $values): never
    {
        $sponsor = new Sponsor(
            id: $id,
            name: $values['name'],
            logo: $values['logo'] !== '' ? $values['logo'] : null,
            website: $values['website'] !== '' ? $values['website'] : null,
            contactEmail: $values['contact_email'] !== '' ? $values['contact_email'] : null
        );
        if ($id === 0) {
            $this->sponsors->create($sponsor);
            AuthController::flash('Sponsor created.');
        } else {
            $this->sponsors->update($sponsor);
            AuthController::flash('Sponsor updated.');
        }
        EventModuleRequest::redirect('admin_sponsors');
    }

    private function delete(int $id): never
    {
        try {
            $this->sponsors->delete($id);
            AuthController::flash('Sponsor deleted.');
        } catch (DomainException $error) {
            AuthController::flash($error->getMessage(), 'error');
        }
        EventModuleRequest::redirect('admin_sponsors');
    }
}
