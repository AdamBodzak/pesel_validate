<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\UserInterface\ViewModel;

use App\Person\Application\ListPersons\PersonListItemReadModel;
use App\Person\Model\Enum\GenderEnum;
use App\Person\UserInterface\ViewModel\PersonListItemViewModel;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PersonListItemViewModelTest extends TestCase
{
    public function testMasksPeselAndPreparesDataForView(): void
    {
        $viewModel = PersonListItemViewModel::fromReadModel(new PersonListItemReadModel(
            '1f0a1c2e-0000-6000-8000-000000000000',
            'Jan',
            'Kowalski',
            '44051401359',
            new DateTimeImmutable('1944-05-14'),
            GenderEnum::Male,
            new DateTimeImmutable('2026-09-29 12:00:00'),
        ));

        self::assertSame('440514*****', $viewModel->maskedPesel);
        self::assertSame('Jan', $viewModel->firstName);
        self::assertSame('Kowalski', $viewModel->lastName);
        self::assertSame('person.gender.male', $viewModel->genderLabel);
        self::assertSame('1944-05-14', $viewModel->birthDate->format('Y-m-d'));
        self::assertSame('2026-09-29 12:00:00', $viewModel->createdAt->format('Y-m-d H:i:s'));
    }

    public function testDoesNotExposeFullPesel(): void
    {
        $viewModel = PersonListItemViewModel::fromReadModel(new PersonListItemReadModel(
            '1f0a1c2e-0000-6000-8000-000000000000',
            'Anna',
            'Nowak',
            '85831512348',
            new DateTimeImmutable('1885-03-15'),
            GenderEnum::Female,
            new DateTimeImmutable('2026-09-29 12:00:00'),
        ));

        self::assertStringNotContainsString('85831512348', serialize(get_object_vars($viewModel)));
    }
}
