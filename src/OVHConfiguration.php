<?php

namespace Sausin\LaravelOvh;

use BadMethodCallException;

class OVHConfiguration
{
    /** @var string */
    protected $authUrl;

    /** @var string */
    protected $projectId;

    /** @var string */
    protected $region;

    /** @var string */
    protected $userDomain;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $container;

    /** @var string|null */
    private $tempUrlKey;

    /** @var string|null */
    private $endpoint;

    /** @var string|null */
    private $swiftLargeObjectThreshold;

    /** @var string|null */
    private $swiftSegmentSize;

    /** @var string|null */
    private $swiftSegmentContainer;

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
        ?string $swiftSegmentContainer
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
            $config['swiftSegmentContainer'] ?? null
        );
    }

    /**
     * Returns the Project Auth Server URL.
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    /**
     * Returns the Project Id.
     *
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }

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
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * Returns the User Domain in the Project.
     * OVH uses the "Default" region by... default...
     *
     * @return string
     */
    public function getUserDomain(): string
    {
        return $this->userDomain;
    }

    /**
     * Returns the Project Username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Returns the Password for the current User.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Returns the name of the desired Container.
     *
     * @return string
     */
    public function getContainer(): string
    {
        return $this->container;
    }

    /**
     * Returns the pre-assigned Temp Url Key of either the Container or Project.
     * This is used to generate Temporary Access Urls for files in the container.
     *
     * @return string|null
     */
    public function getTempUrlKey(): ?string
    {
        return $this->tempUrlKey;
    }

    /**
     * Returns the Custom Endpoint configured for the container.
     *
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * Returns Object Threshold, used while uploading large files.
     *
     * @return string|null
     */
    public function getSwiftLargeObjectThreshold(): ?string
    {
        return $this->swiftLargeObjectThreshold;
    }

    /**
     * Returns Object Segment Size, used while uploading large files.
     *
     * @return string|null
     */
    public function getSwiftSegmentSize(): ?string
    {
        return $this->swiftSegmentSize;
    }

    /**
     * Returns Container Segment, used while uploading large files.
     *
     * @return string|null
     */
    public function getSwiftSegmentContainer(): ?string
    {
        return $this->swiftSegmentContainer;
    }
}
