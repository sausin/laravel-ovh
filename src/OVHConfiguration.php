<?php

namespace Sausin\LaravelOvh;

use BadMethodCallException;

class OVHConfiguration
{
    /**
     * Returns the Project Auth Server URL.
     *
     * @return string
     */
    public string $authUrl;

    /**
     * Returns the Project ID.
     *
     * @return string
     */
    public string $projectId;

    /**
     * Returns the Project Region.
     * It could be one of the following:
     *  - BHS
     *  - DE
     *  - GRA
     *  - SBG
     *  - UK
     *  - WAW
     * Make sure to check your container's region at OVH's Dashboard.
     *
     * @return string
     */
    public string $region;

    /**
     * Returns the User Domain in the Project.
     * OVH uses the "Default" region by... default...
     *
     * @return string
     */
    public string $userDomain;

    /**
     * Returns the Project Username.
     *
     * @return string
     */
    public string $username;

    /**
     * Returns the Password for the current User.
     *
     * @return string
     */
    public string $password;

    /**
     * Returns the name of the desired Container.
     *
     * @return string
     */
    public string $container;

    /**
     * Returns the pre-assigned Temp Url Key of either the Container or Project.
     * This is used to generate Temporary Access Urls for files in the container.
     *
     * Returns NULL if there's no Temp Url Key specified.
     *
     * @return string|null
     */
    public ?string $tempUrlKey;

    /**
     * Returns the Custom Endpoint configured for the container.
     *
     * Returns NULL if there's no Custom Endpoint.
     *
     * @return string|null
     */
    public ?string $endpoint;

    /**
     * Returns Object Threshold, used while uploading large files.
     *
     * Returns NULL if disabled.
     *
     * @return string|null
     */
    public ?string $swiftLargeObjectThreshold;

    /**
     * Returns Object Segment Size, used while uploading large files.
     *
     * Returns NULL if disabled.
     *
     * @return string|null
     */
    public ?string $swiftSegmentSize;

    /**
     * Returns Segment Container Name, used while uploading large files.
     *
     * Returns NULL if disabled. Will use Container Name.
     *
     * @return string|null
     */
    public ?string $swiftSegmentContainer;

    /**
     * Returns the time in seconds on which an object should
     * be deleted from de Container after being uploaded.
     *
     * Returns NULL if uploaded objects should not be deleted
     * by default.
     *
     * @return int|null
     */
    public ?int $deleteAfter;

    /**
     * OVHConfiguration constructor.
     *
     * @param string $authUrl
     * @param string $projectId
     * @param string $region
     * @param string $userDomain
     * @param string $username
     * @param string $password
     * @param string $container
     * @param string|null $tempUrlKey
     * @param string|null $endpoint
     * @param string|null $swiftLargeObjectThreshold
     * @param string|null $swiftSegmentSize
     * @param string|null $swiftSegmentContainer
     * @param int|null $deleteAfter
     */
    public function __construct(
        string $authUrl,
        string $projectId,
        string $region,
        string $userDomain,
        string $username,
        string $password,
        string $container,
        ?string $tempUrlKey,
        ?string $endpoint,
        ?string $swiftLargeObjectThreshold,
        ?string $swiftSegmentSize,
        ?string $swiftSegmentContainer,
        ?int $deleteAfter
    ) {
        $this->authUrl = $authUrl;
        $this->projectId = $projectId;
        $this->region = $region;
        $this->userDomain = $userDomain;
        $this->username = $username;
        $this->password = $password;
        $this->container = $container;
        $this->tempUrlKey = $tempUrlKey;
        $this->endpoint = $endpoint;
        $this->swiftLargeObjectThreshold = $swiftLargeObjectThreshold;
        $this->swiftSegmentSize = $swiftSegmentSize;
        $this->swiftSegmentContainer = $swiftSegmentContainer;
        $this->deleteAfter = $deleteAfter;
    }

    /**
     * Creates a new OVHConfiguration instance.
     *
     * @param array $config
     * @return static
     */
    public static function make(array $config): self
    {
        $neededKeys = ['authUrl', 'projectId', 'region', 'userDomain', 'username', 'password', 'container', 'tempUrlKey', 'endpoint'];
        $missingKeys = array_diff($neededKeys, array_keys($config));

        if (count($missingKeys) > 0) {
            throw new BadMethodCallException('The following keys must be provided: '.implode(', ', $missingKeys));
        }

        return new self(
            $config['authUrl'],
            $config['projectId'],
            $config['region'],
            $config['userDomain'],
            $config['username'],
            $config['password'],
            $config['container'],
            $config['tempUrlKey'],
            $config['endpoint'],
            $config['swiftLargeObjectThreshold'] ?? null,
            $config['swiftSegmentSize'] ?? null,
            $config['swiftSegmentContainer'] ?? null,
            $config['deleteAfter'] ?? null
        );
    }
}
