<?php
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use LeagueTests\Stubs\AccessTokenEntity;
use LeagueTests\Stubs\AuthCodeEntity;
use LeagueTests\Stubs\ClientEntity;
use LeagueTests\Stubs\StubResponseType;
use LeagueTests\Stubs\UserEntity;
use Orchestra\Testbench\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class AuthorizationServerTest extends TestCase
{
    use \Oauth2Tests\ConfigureTestcase;
    public function testRespondToRequestInvalidGrantType()
    {
        try {
            Oauth2::getServer()->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response);
        } catch (OAuthServerException $e) {
            $this->assertEquals('unsupported_grant_type', $e->getErrorType());
            $this->assertEquals(400, $e->getHttpStatusCode());
        }
    }

    public function testRespondToRequest()
    {


        $_POST['grant_type'] = 'client_credentials';
        $_POST['client_id'] = 'foo';
        $_POST['client_secret'] = 'bar';
        $response = Oauth2::getServer()->respondToAccessTokenRequest(ServerRequestFactory::fromGlobals(), new Response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetResponseType()
    {
        $abstractGrantReflection = new \ReflectionClass(Oauth2::getServer());
        $method = $abstractGrantReflection->getMethod('getResponseType');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(Oauth2::getServer()) instanceof BearerTokenResponse);
    }

    public function testCompleteAuthorizationRequest()
    {
        $clientRepository = $this->getMock(ClientRepositoryInterface::class);

        $server = new AuthorizationServer(
            $clientRepository,
            $this->getMock(AccessTokenRepositoryInterface::class),
            $this->getMock(ScopeRepositoryInterface::class),
            'file://' . __DIR__ . '/Stubs/private.key',
            'file://' . __DIR__ . '/Stubs/public.key'
        );

        $authCodeRepository = $this->getMockBuilder(AuthCodeRepositoryInterface::class)->getMock();
        $authCodeRepository->method('getNewAuthCode')->willReturn(new AuthCodeEntity());

        $grant = new AuthCodeGrant(
            $authCodeRepository,
            $this->getMock(RefreshTokenRepositoryInterface::class),
            new \DateInterval('PT10M')
        );

        $grant->setPrivateKey(new CryptKey('file://' . __DIR__ . '/Stubs/private.key'));
        $grant->setPublicKey(new CryptKey('file://' . __DIR__ . '/Stubs/public.key'));

        $server->enableGrantType($grant);

        $authRequest = new AuthorizationRequest();
        $authRequest->setAuthorizationApproved(true);
        $authRequest->setClient(new ClientEntity());
        $authRequest->setGrantTypeId('authorization_code');
        $authRequest->setUser(new UserEntity());

        $this->assertTrue(
            $server->completeAuthorizationRequest($authRequest, new Response) instanceof ResponseInterface
        );
    }

    public function testValidateAuthorizationRequest()
    {
        $client = new ClientEntity();
        $clientRepositoryMock = $this->getMockBuilder(ClientRepositoryInterface::class)->getMock();
        $clientRepositoryMock->method('getClientEntity')->willReturn($client);

        $grant = new AuthCodeGrant(
            $this->getMock(AuthCodeRepositoryInterface::class),
            $this->getMock(RefreshTokenRepositoryInterface::class),
            new \DateInterval('PT10M')
        );
        $grant->setClientRepository($clientRepositoryMock);

        $server = new AuthorizationServer(
            $clientRepositoryMock,
            $this->getMock(AccessTokenRepositoryInterface::class),
            $this->getMock(ScopeRepositoryInterface::class),
            'file://' . __DIR__ . '/Stubs/private.key',
            'file://' . __DIR__ . '/Stubs/public.key'
        );
        $server->enableGrantType($grant);

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
            ]
        );

        $this->assertTrue($server->validateAuthorizationRequest($request) instanceof AuthorizationRequest);
    }

    /**
     * @expectedException  \League\OAuth2\Server\Exception\OAuthServerException
     * @expectedExceptionCode 2
     */
    public function testValidateAuthorizationRequestUnregistered()
    {
        $server = new AuthorizationServer(
            $this->getMock(ClientRepositoryInterface::class),
            $this->getMock(AccessTokenRepositoryInterface::class),
            $this->getMock(ScopeRepositoryInterface::class),
            'file://' . __DIR__ . '/Stubs/private.key',
            'file://' . __DIR__ . '/Stubs/public.key'
        );

        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [
                'response_type' => 'code',
                'client_id'     => 'foo',
            ]
        );

        $server->validateAuthorizationRequest($request);
    }
}
