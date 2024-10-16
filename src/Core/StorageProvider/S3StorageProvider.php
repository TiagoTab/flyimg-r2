<?php

namespace Core\StorageProvider;

use Aws\S3\S3Client;
use Core\Exception\MissingParamsException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Storage class to manage Storage provider from FlySystem
 *
 * Class StorageProvider
 * @package Core\Provider
 */
class S3StorageProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     *
     * @throws MissingParamsException
     */
    public function register(Container $app)
    {
        $s3Params = $app['params']->parameterByKey('aws_s3');
        if (in_array("", $s3Params)) {
            throw new MissingParamsException("One of AWS S3 parameters in empty ! ");
        }

        $this->registerS3ServiceProvider($app, $s3Params);
        $app['flysystems']['file_path_resolver'] = function () use ($s3Params) {
            return $s3Params['endpoint'];
        };
    }

    /**
     * @param Container $app
     * @param array     $s3Params
     *
     * @return Container
     */
    protected function registerS3ServiceProvider(Container $app, array $s3Params): Container
    {
        $clientParams = [
            'credentials' => [
                'key' => $s3Params['access_id'],
                'secret' => $s3Params['secret_key'],
            ],
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => $s3Params['endpoint'],
            'bucket_endpoint' => false
        ];

        $app->register(
            new FlysystemServiceProvider(),
            [
                'flysystem.filesystems' => [
                    'storage_handler' => [
                        'adapter' => 'League\Flysystem\AwsS3V3\AwsS3V3Adapter',
                        'args' => [
                            new S3Client($clientParams),
                            $s3Params['bucket_name'],
                            $s3Params['bucket_path']
                        ],
                    ],
                ],
            ]
        );

        return $app;
    }
}
