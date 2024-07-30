<?php

namespace AsyncAws\Sqs\Input;

use AsyncAws\Core\Exception\InvalidArgument;
use AsyncAws\Core\Input;
use AsyncAws\Core\Request;
use AsyncAws\Core\Stream\StreamFactory;
use AsyncAws\Sqs\Enum\QueueAttributeName;

final class CreateQueueRequest extends Input
{
    /**
     * The name of the new queue. The following limits apply to this name:
     *
     * - A queue name can have up to 80 characters.
     * - Valid values: alphanumeric characters, hyphens (`-`), and underscores (`_`).
     * - A FIFO queue name must end with the `.fifo` suffix.
     *
     * Queue URLs and names are case-sensitive.
     *
     * @required
     *
     * @var string|null
     */
    private $queueName;

    /**
     * A map of attributes with their corresponding values.
     *
     * The following lists the names, descriptions, and values of the special request parameters that the `CreateQueue`
     * action uses:
     *
     * - `DelaySeconds` – The length of time, in seconds, for which the delivery of all messages in the queue is delayed.
     *   Valid values: An integer from 0 to 900 seconds (15 minutes). Default: 0.
     * - `MaximumMessageSize` – The limit of how many bytes a message can contain before Amazon SQS rejects it. Valid
     *   values: An integer from 1,024 bytes (1 KiB) to 262,144 bytes (256 KiB). Default: 262,144 (256 KiB).
     * - `MessageRetentionPeriod` – The length of time, in seconds, for which Amazon SQS retains a message. Valid values:
     *   An integer from 60 seconds (1 minute) to 1,209,600 seconds (14 days). Default: 345,600 (4 days). When you change a
     *   queue's attributes, the change can take up to 60 seconds for most of the attributes to propagate throughout the
     *   Amazon SQS system. Changes made to the `MessageRetentionPeriod` attribute can take up to 15 minutes and will impact
     *   existing messages in the queue potentially causing them to be expired and deleted if the `MessageRetentionPeriod`
     *   is reduced below the age of existing messages.
     * - `Policy` – The queue's policy. A valid Amazon Web Services policy. For more information about policy structure,
     *   see Overview of Amazon Web Services IAM Policies [^1] in the *IAM User Guide*.
     * - `ReceiveMessageWaitTimeSeconds` – The length of time, in seconds, for which a `ReceiveMessage` action waits for a
     *   message to arrive. Valid values: An integer from 0 to 20 (seconds). Default: 0.
     * - `VisibilityTimeout` – The visibility timeout for the queue, in seconds. Valid values: An integer from 0 to 43,200
     *   (12 hours). Default: 30. For more information about the visibility timeout, see Visibility Timeout [^2] in the
     *   *Amazon SQS Developer Guide*.
     *
     * The following attributes apply only to dead-letter queues: [^3]
     *
     * - `RedrivePolicy` – The string that includes the parameters for the dead-letter queue functionality of the source
     *   queue as a JSON object. The parameters are as follows:
     *
     *   - `deadLetterTargetArn` – The Amazon Resource Name (ARN) of the dead-letter queue to which Amazon SQS moves
     *     messages after the value of `maxReceiveCount` is exceeded.
     *   - `maxReceiveCount` – The number of times a message is delivered to the source queue before being moved to the
     *     dead-letter queue. Default: 10. When the `ReceiveCount` for a message exceeds the `maxReceiveCount` for a queue,
     *     Amazon SQS moves the message to the dead-letter-queue.
     *
     * - `RedriveAllowPolicy` – The string that includes the parameters for the permissions for the dead-letter queue
     *   redrive permission and which source queues can specify dead-letter queues as a JSON object. The parameters are as
     *   follows:
     *
     *   - `redrivePermission` – The permission type that defines which source queues can specify the current queue as the
     *     dead-letter queue. Valid values are:
     *
     *     - `allowAll` – (Default) Any source queues in this Amazon Web Services account in the same Region can specify
     *       this queue as the dead-letter queue.
     *     - `denyAll` – No source queues can specify this queue as the dead-letter queue.
     *     - `byQueue` – Only queues specified by the `sourceQueueArns` parameter can specify this queue as the
     *       dead-letter queue.
     *
     *   - `sourceQueueArns` – The Amazon Resource Names (ARN)s of the source queues that can specify this queue as the
     *     dead-letter queue and redrive messages. You can specify this parameter only when the `redrivePermission`
     *     parameter is set to `byQueue`. You can specify up to 10 source queue ARNs. To allow more than 10 source queues to
     *     specify dead-letter queues, set the `redrivePermission` parameter to `allowAll`.
     *
     *
     * > The dead-letter queue of a FIFO queue must also be a FIFO queue. Similarly, the dead-letter queue of a standard
     * > queue must also be a standard queue.
     *
     * The following attributes apply only to server-side-encryption [^4]:
     *
     * - `KmsMasterKeyId` – The ID of an Amazon Web Services managed customer master key (CMK) for Amazon SQS or a custom
     *   CMK. For more information, see Key Terms [^5]. While the alias of the Amazon Web Services managed CMK for Amazon
     *   SQS is always `alias/aws/sqs`, the alias of a custom CMK can, for example, be `alias/*MyAlias*`. For more examples,
     *   see KeyId [^6] in the *Key Management Service API Reference*.
     * - `KmsDataKeyReusePeriodSeconds` – The length of time, in seconds, for which Amazon SQS can reuse a data key [^7]
     *   to encrypt or decrypt messages before calling KMS again. An integer representing seconds, between 60 seconds (1
     *   minute) and 86,400 seconds (24 hours). Default: 300 (5 minutes). A shorter time period provides better security but
     *   results in more calls to KMS which might incur charges after Free Tier. For more information, see How Does the Data
     *   Key Reuse Period Work? [^8]
     * - `SqsManagedSseEnabled` – Enables server-side queue encryption using SQS owned encryption keys. Only one
     *   server-side encryption option is supported per queue (for example, SSE-KMS [^9] or SSE-SQS [^10]).
     *
     * The following attributes apply only to FIFO (first-in-first-out) queues [^11]:
     *
     * - `FifoQueue` – Designates a queue as FIFO. Valid values are `true` and `false`. If you don't specify the
     *   `FifoQueue` attribute, Amazon SQS creates a standard queue. You can provide this attribute only during queue
     *   creation. You can't change it for an existing queue. When you set this attribute, you must also provide the
     *   `MessageGroupId` for your messages explicitly.
     *
     *   For more information, see FIFO queue logic [^12] in the *Amazon SQS Developer Guide*.
     * - `ContentBasedDeduplication` – Enables content-based deduplication. Valid values are `true` and `false`. For more
     *   information, see Exactly-once processing [^13] in the *Amazon SQS Developer Guide*. Note the following:
     *
     *   - Every message must have a unique `MessageDeduplicationId`.
     *
     *     - You may provide a `MessageDeduplicationId` explicitly.
     *     - If you aren't able to provide a `MessageDeduplicationId` and you enable `ContentBasedDeduplication` for your
     *       queue, Amazon SQS uses a SHA-256 hash to generate the `MessageDeduplicationId` using the body of the message
     *       (but not the attributes of the message).
     *     - If you don't provide a `MessageDeduplicationId` and the queue doesn't have `ContentBasedDeduplication` set, the
     *       action fails with an error.
     *     - If the queue has `ContentBasedDeduplication` set, your `MessageDeduplicationId` overrides the generated one.
     *
     *   - When `ContentBasedDeduplication` is in effect, messages with identical content sent within the deduplication
     *     interval are treated as duplicates and only one copy of the message is delivered.
     *   - If you send one message with `ContentBasedDeduplication` enabled and then another message with a
     *     `MessageDeduplicationId` that is the same as the one generated for the first `MessageDeduplicationId`, the two
     *     messages are treated as duplicates and only one copy of the message is delivered.
     *
     *
     * The following attributes apply only to high throughput for FIFO queues [^14]:
     *
     * - `DeduplicationScope` – Specifies whether message deduplication occurs at the message group or queue level. Valid
     *   values are `messageGroup` and `queue`.
     * - `FifoThroughputLimit` – Specifies whether the FIFO queue throughput quota applies to the entire queue or per
     *   message group. Valid values are `perQueue` and `perMessageGroupId`. The `perMessageGroupId` value is allowed only
     *   when the value for `DeduplicationScope` is `messageGroup`.
     *
     * To enable high throughput for FIFO queues, do the following:
     *
     * - Set `DeduplicationScope` to `messageGroup`.
     * - Set `FifoThroughputLimit` to `perMessageGroupId`.
     *
     * If you set these attributes to anything other than the values shown for enabling high throughput, normal throughput
     * is in effect and deduplication occurs as specified.
     *
     * For information on throughput quotas, see Quotas related to messages [^15] in the *Amazon SQS Developer Guide*.
     *
     * [^1]: https://docs.aws.amazon.com/IAM/latest/UserGuide/PoliciesOverview.html
     * [^2]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-visibility-timeout.html
     * [^3]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-dead-letter-queues.html
     * [^4]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-server-side-encryption.html
     * [^5]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-server-side-encryption.html#sqs-sse-key-terms
     * [^6]: https://docs.aws.amazon.com/kms/latest/APIReference/API_DescribeKey.html#API_DescribeKey_RequestParameters
     * [^7]: https://docs.aws.amazon.com/kms/latest/developerguide/concepts.html#data-keys
     * [^8]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-server-side-encryption.html#sqs-how-does-the-data-key-reuse-period-work
     * [^9]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-configure-sse-existing-queue.html
     * [^10]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-configure-sqs-sse-queue.html
     * [^11]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/FIFO-queues.html
     * [^12]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/FIFO-queues-understanding-logic.html
     * [^13]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/FIFO-queues-exactly-once-processing.html
     * [^14]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/high-throughput-fifo.html
     * [^15]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/quotas-messages.html
     *
     * @var array<QueueAttributeName::*, string>|null
     */
    private $attributes;

    /**
     * Add cost allocation tags to the specified Amazon SQS queue. For an overview, see Tagging Your Amazon SQS Queues [^1]
     * in the *Amazon SQS Developer Guide*.
     *
     * When you use queue tags, keep the following guidelines in mind:
     *
     * - Adding more than 50 tags to a queue isn't recommended.
     * - Tags don't have any semantic meaning. Amazon SQS interprets tags as character strings.
     * - Tags are case-sensitive.
     * - A new tag with a key identical to that of an existing tag overwrites the existing tag.
     *
     * For a full list of tag restrictions, see Quotas related to queues [^2] in the *Amazon SQS Developer Guide*.
     *
     * > To be able to tag a queue on creation, you must have the `sqs:CreateQueue` and `sqs:TagQueue` permissions.
     * >
     * > Cross-account permissions don't apply to this action. For more information, see Grant cross-account permissions to
     * > a role and a username [^3] in the *Amazon SQS Developer Guide*.
     *
     * [^1]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-queue-tags.html
     * [^2]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-limits.html#limits-queues
     * [^3]: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-customer-managed-policy-examples.html#grant-cross-account-permissions-to-role-and-user-name
     *
     * @var array<string, string>|null
     */
    private $tags;

    /**
     * @param array{
     *   QueueName?: string,
     *   Attributes?: null|array<QueueAttributeName::*, string>,
     *   tags?: null|array<string, string>,
     *   '@region'?: string|null,
     * } $input
     */
    public function __construct(array $input = [])
    {
        $this->queueName = $input['QueueName'] ?? null;
        $this->attributes = $input['Attributes'] ?? null;
        $this->tags = $input['tags'] ?? null;
        parent::__construct($input);
    }

    /**
     * @param array{
     *   QueueName?: string,
     *   Attributes?: null|array<QueueAttributeName::*, string>,
     *   tags?: null|array<string, string>,
     *   '@region'?: string|null,
     * }|CreateQueueRequest $input
     */
    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    /**
     * @return array<QueueAttributeName::*, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }

    public function getQueueName(): ?string
    {
        return $this->queueName;
    }

    /**
     * @return array<string, string>
     */
    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    /**
     * @internal
     */
    public function request(): Request
    {
        // Prepare headers
        $headers = [
            'Content-Type' => 'application/x-amz-json-1.0',
            'X-Amz-Target' => 'AmazonSQS.CreateQueue',
            'Accept' => 'application/json',
        ];

        // Prepare query
        $query = [];

        // Prepare URI
        $uriString = '/';

        // Prepare Body
        $bodyPayload = $this->requestBody();
        $body = empty($bodyPayload) ? '{}' : json_encode($bodyPayload, 4194304);

        // Return the Request
        return new Request('POST', $uriString, $query, $headers, StreamFactory::create($body));
    }

    /**
     * @param array<QueueAttributeName::*, string> $value
     */
    public function setAttributes(array $value): self
    {
        $this->attributes = $value;

        return $this;
    }

    public function setQueueName(?string $value): self
    {
        $this->queueName = $value;

        return $this;
    }

    /**
     * @param array<string, string> $value
     */
    public function setTags(array $value): self
    {
        $this->tags = $value;

        return $this;
    }

    private function requestBody(): array
    {
        $payload = [];
        if (null === $v = $this->queueName) {
            throw new InvalidArgument(\sprintf('Missing parameter "QueueName" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['QueueName'] = $v;
        if (null !== $v = $this->attributes) {
            if (empty($v)) {
                $payload['Attributes'] = new \stdClass();
            } else {
                $payload['Attributes'] = [];
                foreach ($v as $name => $mv) {
                    if (!QueueAttributeName::exists($name)) {
                        throw new InvalidArgument(\sprintf('Invalid key for "%s". The value "%s" is not a valid "QueueAttributeName".', __CLASS__, $name));
                    }
                    $payload['Attributes'][$name] = $mv;
                }
            }
        }
        if (null !== $v = $this->tags) {
            if (empty($v)) {
                $payload['tags'] = new \stdClass();
            } else {
                $payload['tags'] = [];
                foreach ($v as $name => $mv) {
                    $payload['tags'][$name] = $mv;
                }
            }
        }

        return $payload;
    }
}
