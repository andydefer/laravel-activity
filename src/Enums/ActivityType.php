<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum ActivityType: string implements EnumerableInterface
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';

    case LOGGED_IN = 'logged_in';
    case LOGGED_OUT = 'logged_out';
    case REGISTERED = 'registered';
    case PASSWORD_CHANGED = 'password_changed';
    case EMAIL_VERIFIED = 'email_verified';

    case VIEWED = 'viewed';
    case DOWNLOADED = 'downloaded';
    case SHARED = 'shared';

    case COMMENTED = 'commented';
    case LIKED = 'liked';
    case RATED = 'rated';

    case PAYMENT_INITIATED = 'payment_initiated';
    case PAYMENT_COMPLETED = 'payment_completed';
    case PAYMENT_FAILED = 'payment_failed';
    case PAYMENT_REFUNDED = 'payment_refunded';

    case OTHER = 'other';

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATED => 'Créé',
            self::UPDATED => 'Modifié',
            self::DELETED => 'Supprimé',
            self::RESTORED => 'Restauré',
            self::LOGGED_IN => 'Connexion',
            self::LOGGED_OUT => 'Déconnexion',
            self::REGISTERED => 'Inscription',
            self::PASSWORD_CHANGED => 'Mot de passe modifié',
            self::EMAIL_VERIFIED => 'Email vérifié',
            self::VIEWED => 'Consulté',
            self::DOWNLOADED => 'Téléchargé',
            self::SHARED => 'Partagé',
            self::COMMENTED => 'Commenté',
            self::LIKED => 'Aimé',
            self::RATED => 'Noté',
            self::PAYMENT_INITIATED => 'Paiement initié',
            self::PAYMENT_COMPLETED => 'Paiement complété',
            self::PAYMENT_FAILED => 'Paiement échoué',
            self::PAYMENT_REFUNDED => 'Paiement remboursé',
            self::OTHER => 'Autre',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CREATED, self::REGISTERED => 'green',
            self::UPDATED, self::PASSWORD_CHANGED => 'blue',
            self::DELETED => 'red',
            self::RESTORED => 'teal',
            self::LOGGED_IN => 'cyan',
            self::LOGGED_OUT => 'gray',
            self::EMAIL_VERIFIED => 'green',
            self::VIEWED, self::DOWNLOADED, self::SHARED => 'purple',
            self::COMMENTED, self::LIKED, self::RATED => 'amber',
            self::PAYMENT_INITIATED => 'orange',
            self::PAYMENT_COMPLETED => 'green',
            self::PAYMENT_FAILED => 'red',
            self::PAYMENT_REFUNDED => 'dark-gray',
            self::OTHER => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
