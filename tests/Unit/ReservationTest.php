<?php
namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ReservationTest extends TestCase
{
    public function testPrixTotalEstCorrect(): void
    {
        $prixPlace = 9.00;
        $nb = 3;
        $this->assertSame(27.00, $prixPlace * $nb);
    }

    public function testNoteSur5Valide(): void
    {
        $valide = fn(int $n) => $n >= 1 && $n <= 5;
        $this->assertTrue($valide(1));
        $this->assertTrue($valide(5));
        $this->assertFalse($valide(0));
        $this->assertFalse($valide(6));
    }

    public function testNbSiegesDoitCorrespondreAuNombreDePlaces(): void
    {
        $nombrePlaces = 3;
        $sieges = ['A1','A2','A3'];
        $this->assertCount($nombrePlaces, $sieges);
    }
}
