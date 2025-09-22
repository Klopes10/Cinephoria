<?php
namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

final class LoginTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;
    private RouterInterface $router;

    protected function setUp(): void
    {
        parent::setUp();

        // IMPORTANT : un seul boot via createClient()
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $c = static::getContainer();

        $this->em     = $c->get(EntityManagerInterface::class);
        $this->hasher = $c->get(UserPasswordHasherInterface::class);
        $this->router = $c->get(RouterInterface::class);

        // Base propre (évite la collision d’ID sur "user")
        $this->em->getConnection()->executeStatement('TRUNCATE "user" RESTART IDENTITY CASCADE');
    }

    public function testLoginPageLoads(): void
    {
        $url = $this->router->generate('app_auth'); // GET /auth
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful('La page /auth doit répondre 200');
        $this->assertGreaterThan(
            0,
            $this->client->getCrawler()->filter('form')->count(),
            'Un formulaire doit être présent.'
        );
    }

    public function testLoginWithValidCredentials(): void
    {
        // 1) Crée un user en base
        $email    = 'login.test+'.uniqid().'@example.com';
        $password = 'P@ssw0rd!';

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_USER'])
            ->setName('Test')
            ->setForname('Login')
            ->setUsername('test_login')
            ->setCreateAt(new \DateTimeImmutable());

        $user->setPassword($this->hasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();

        // 2) Va sur /auth et soumets
        $url = $this->router->generate('app_auth');
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'email'    => $email,
            'password' => $password,
        ]);
        $this->client->submit($form);

        // 3) Redirection puis 200
        $this->assertTrue($this->client->getResponse()->isRedirect(), 'Après login, on doit être redirigé');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $url = $this->router->generate('app_auth');
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'email'    => 'wrong@example.com',
            'password' => 'badpassword',
        ]);
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect(), 'Échec login => redirection attendue');
        $this->client->followRedirect();
        
    }
}
