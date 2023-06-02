<?php

namespace AsyncAws\Athena\ValueObject;

use AsyncAws\Athena\Enum\SessionState;

/**
 * Contains information about the status of the session.
 */
final class SessionStatus
{
    /**
     * The date and time that the session started.
     */
    private $startDateTime;

    /**
     * The most recent date and time that the session was modified.
     */
    private $lastModifiedDateTime;

    /**
     * The date and time that the session ended.
     */
    private $endDateTime;

    /**
     * The date and time starting at which the session became idle. Can be empty if the session is not currently idle.
     */
    private $idleSinceDateTime;

    /**
     * The state of the session. A description of each state follows.
     */
    private $state;

    /**
     * The reason for the session state change (for example, canceled because the session was terminated).
     */
    private $stateChangeReason;

    /**
     * @param array{
     *   StartDateTime?: null|\DateTimeImmutable,
     *   LastModifiedDateTime?: null|\DateTimeImmutable,
     *   EndDateTime?: null|\DateTimeImmutable,
     *   IdleSinceDateTime?: null|\DateTimeImmutable,
     *   State?: null|SessionState::*,
     *   StateChangeReason?: null|string,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->startDateTime = $input['StartDateTime'] ?? null;
        $this->lastModifiedDateTime = $input['LastModifiedDateTime'] ?? null;
        $this->endDateTime = $input['EndDateTime'] ?? null;
        $this->idleSinceDateTime = $input['IdleSinceDateTime'] ?? null;
        $this->state = $input['State'] ?? null;
        $this->stateChangeReason = $input['StateChangeReason'] ?? null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getEndDateTime(): ?\DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function getIdleSinceDateTime(): ?\DateTimeImmutable
    {
        return $this->idleSinceDateTime;
    }

    public function getLastModifiedDateTime(): ?\DateTimeImmutable
    {
        return $this->lastModifiedDateTime;
    }

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->startDateTime;
    }

    /**
     * @return SessionState::*|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    public function getStateChangeReason(): ?string
    {
        return $this->stateChangeReason;
    }
}