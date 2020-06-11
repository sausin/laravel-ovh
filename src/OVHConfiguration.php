<?php

namespace Sausin\LaravelOvh;

class OVHConfiguration
{
    /**
     * Returns the Project Auth Server URL.
     *
     * @return string
     */
    protected string $authUrl;

    /**
     * Returns the Project ID.
     *
     * @return string
     */
    protected string $projectId;

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
    protected string $region;

    /**
     * Returns the User Domain in the Project.
     * OVH uses the "Default" region by... default...
     *
     * @return string
     */
    protected string $userDomain;

    /**
     * Returns the Project Username.
     *
     * @return string
     */
    protected string $username;

    /**
     * Returns the Password for the current User.
     *
     * @return string
     */
    protected string $password;

    /**
     * Returns the name of the desired Container.
     *
     * @return string
     */
    protected string $containerName;

    /**
     * OPTIONAL.
     *
     * Returns the pre-assigned Temp Url Key of either the Container or Project.
     * This is used to generate Temporary Access Urls for files in the container.
     *
     * Returns NULL if there's no Temp Url Key specified.
     *
     * @return string|null
     */
    protected ?string $tempUrlKey;

    /**
     * OPTIONAL.
     *
     * Returns the Custom Endpoint configured for the container.
     *
     * Returns NULL if there's no Custom Endpoint.
     *
     * @return string|null
     */
    protected ?string $endpoint;

    /**
     * OPTIONAL.
     *
     * Returns Object Threshold, used while uploading large files.
     *
     * Returns NULL if disabled.
     *
     * @return string|null
     */
    protected ?string $swiftLargeObjectThreshold;

    /**
     * OPTIONAL.
     *
     * Returns Object Segment Size, used while uploading large files.
     *
     * Returns NULL if disabled.
     *
     * @return string|null
     */
    protected ?string $swiftSegmentSize;

    /**
     * OPTIONAL.
     *
     * Returns Segment Container Name, used while uploading large files.
     *
     * Returns NULL if disabled. Will use Container Name.
     *
     * @return string|null
     */
    protected ?string $swiftSegmentContainer;

    /**
     * OPTIONAL.
     *
     * Returns the time in seconds on which an object should
     * be deleted from de Container after being uploaded.
     *
     * Returns NULL if uploaded objects should not be deleted
     * by default.
     *
     * @return int|null
     */
    protected ?int $deleteAfter;

    /**
     * OPTIONAL.
     *
     * Prefixes all paths with this valus. Useful in multi-tenant setups
     *
     * Returns NULL if disabled.
     *
     * @return string|null
     */
    protected ?string $prefix;

    /**
     * OVHConfiguration constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Creates a new OVHConfiguration instance.
     *
     * @param array $config
     * @return static
     */
    public static function make(array $config): self
    {
        $neededKeys = ['authUrl', 'projectId', 'region', 'userDomain', 'username', 'password', 'containerName'];
        $missingKeys = array_diff($neededKeys, array_keys($config));

        if (count($missingKeys) > 0) {
            throw new \BadMethodCallException('The following keys must be provided: '.implode(', ', $missingKeys));
        }

        return (new self())
            ->setAuthUrl($config['authUrl'])
            ->setProjectId($config['projectId'])
            ->setRegion($config['region'])
            ->setUserDomain($config['userDomain'])
            ->setUsername($config['username'])
            ->setPassword($config['password'])
            ->setContainerName($config['containerName'])
            ->setTempUrlKey($config['tempUrlKey'] ?? null)
            ->setEndpoint($config['endpoint'] ?? null)
            ->setSwiftLargeObjectThreshold($config['swiftLargeObjectThreshold'] ?? null)
            ->setSwiftSegmentSize($config['swiftSegmentSize'] ?? null)
            ->setSwiftSegmentContainer($config['swiftSegmentContainer'] ?? null)
            ->setDeleteAfter($config['deleteAfter'] ?? null)
            ->setPrefix($config['prefix'] ?? null);
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    /**
     * @param string $authUrl
     * @return OVHConfiguration
     */
    public function setAuthUrl(string $authUrl): self
    {
        $this->authUrl = $authUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }

    /**
     * @param string $projectId
     * @return OVHConfiguration
     */
    public function setProjectId(string $projectId): self
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return OVHConfiguration
     */
    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserDomain(): string
    {
        return $this->userDomain;
    }

    /**
     * @param string $userDomain
     * @return OVHConfiguration
     */
    public function setUserDomain(string $userDomain): self
    {
        $this->userDomain = $userDomain;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return OVHConfiguration
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return OVHConfiguration
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerName(): string
    {
        return $this->containerName;
    }

    /**
     * @param string $containerName
     * @return OVHConfiguration
     */
    public function setContainerName(string $containerName): self
    {
        $this->containerName = $containerName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTempUrlKey(): ?string
    {
        return $this->tempUrlKey;
    }

    /**
     * @param string|null $tempUrlKey
     * @return OVHConfiguration
     */
    public function setTempUrlKey(?string $tempUrlKey): self
    {
        $this->tempUrlKey = $tempUrlKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * @param string|null $endpoint
     * @return OVHConfiguration
     */
    public function setEndpoint(?string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwiftLargeObjectThreshold(): ?string
    {
        return $this->swiftLargeObjectThreshold;
    }

    /**
     * @param string|null $swiftLargeObjectThreshold
     * @return OVHConfiguration
     */
    public function setSwiftLargeObjectThreshold(?string $swiftLargeObjectThreshold): self
    {
        $this->swiftLargeObjectThreshold = $swiftLargeObjectThreshold;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwiftSegmentSize(): ?string
    {
        return $this->swiftSegmentSize;
    }

    /**
     * @param string|null $swiftSegmentSize
     * @return OVHConfiguration
     */
    public function setSwiftSegmentSize(?string $swiftSegmentSize): self
    {
        $this->swiftSegmentSize = $swiftSegmentSize;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwiftSegmentContainer(): ?string
    {
        return $this->swiftSegmentContainer;
    }

    /**
     * @param string|null $swiftSegmentContainer
     * @return OVHConfiguration
     */
    public function setSwiftSegmentContainer(?string $swiftSegmentContainer): self
    {
        $this->swiftSegmentContainer = $swiftSegmentContainer;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDeleteAfter(): ?int
    {
        return $this->deleteAfter;
    }

    /**
     * @param int|null $deleteAfter
     * @return OVHConfiguration
     */
    public function setDeleteAfter(?int $deleteAfter): self
    {
        $this->deleteAfter = $deleteAfter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     * @return OVHConfiguration
     */
    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
