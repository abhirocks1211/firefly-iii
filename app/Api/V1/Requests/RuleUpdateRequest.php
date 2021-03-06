<?php
/**
 * RuleUpdateRequest.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V1\Requests;

use FireflyIII\Rules\IsBoolean;
use Illuminate\Validation\Validator;
use function is_array;


/**
 * Class RuleUpdateRequest
 *
 */
class RuleUpdateRequest extends Request
{
    /**
     * Authorize logged in users.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Only allow authenticated users
        return auth()->check();
    }

    /**
     * Get all data from the request.
     *
     * @return array
     */
    public function getAll(): array
    {
        $strict         = null;
        $active         = null;
        $stopProcessing = null;
        if (null !== $this->get('active')) {
            $active = $this->boolean('active');
        }
        if (null !== $this->get('strict')) {
            $strict = $this->boolean('strict');
        }
        if (null !== $this->get('stop_processing')) {
            $stopProcessing = $this->boolean('stop_processing');
        }

        $data = [
            'title'            => $this->nullableString('title'),
            'description'      => $this->nullableString('description'),
            'rule_group_id'    => $this->nullableInteger('rule_group_id'),
            'rule_group_title' => $this->nullableString('rule_group_title'),
            'trigger'          => $this->nullableString('trigger'),
            'strict'           => $strict,
            'stop_processing'  => $stopProcessing,
            'active'           => $active,
            'triggers'         => $this->getRuleTriggers(),
            'actions'          => $this->getRuleActions(),
        ];

        return $data;
    }

    /**
     * The rules that the incoming request must be matched against.
     *
     * @return array
     */
    public function rules(): array
    {
        $validTriggers = array_keys(config('firefly.rule-triggers'));
        $validActions  = array_keys(config('firefly.rule-actions'));
        $rule          = $this->route()->parameter('rule');

        // some triggers and actions require text:
        $contextTriggers = implode(',', config('firefly.context-rule-triggers'));
        $contextActions  = implode(',', config('firefly.context-rule-actions'));
        $rules           = [
            'title'                      => sprintf('between:1,100|uniqueObjectForUser:rules,title,%d', $rule->id),
            'description'                => 'between:1,5000|nullable',
            'rule_group_id'              => 'belongsToUser:rule_groups',
            'rule_group_title'           => 'nullable|between:1,255|belongsToUser:rule_groups,title',
            'trigger'                    => 'in:store-journal,update-journal',
            'triggers.*.type'            => 'required|in:' . implode(',', $validTriggers),
            'triggers.*.value'           => 'required_if:actions.*.type,' . $contextTriggers . '|min:1|ruleTriggerValue',
            'triggers.*.stop_processing' => [new IsBoolean],
            'triggers.*.active'          => [new IsBoolean],
            'actions.*.type'             => 'required|in:' . implode(',', $validActions),
            'actions.*.value'            => 'required_if:actions.*.type,' . $contextActions . '|ruleActionValue',
            'actions.*.stop_processing'  => [new IsBoolean],
            'actions.*.active'           => [new IsBoolean],
            'strict'                     => [new IsBoolean],
            'stop_processing'            => [new IsBoolean],
            'active'                     => [new IsBoolean],
        ];

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(
            function (Validator $validator) {
                $this->atLeastOneTrigger($validator);
                $this->atLeastOneAction($validator);
            }
        );
    }

    /**
     * Adds an error to the validator when there are no repetitions in the array of data.
     *
     * @param Validator $validator
     */
    protected function atLeastOneAction(Validator $validator): void
    {
        $data    = $validator->getData();
        $actions = $data['actions'] ?? null;
        // need at least one action
        if (is_array($actions) && 0 === count($actions)) {
            $validator->errors()->add('title', (string)trans('validation.at_least_one_action'));
        }
    }

    /**
     * Adds an error to the validator when there are no repetitions in the array of data.
     *
     * @param Validator $validator
     */
    protected function atLeastOneTrigger(Validator $validator): void
    {
        $data     = $validator->getData();
        $triggers = $data['triggers'] ?? null;
        // need at least one trigger
        if (is_array($triggers) && 0 === count($triggers)) {
            $validator->errors()->add('title', (string)trans('validation.at_least_one_trigger'));
        }
    }

    /**
     * @return array|null
     */
    private function getRuleActions(): ?array
    {
        if (!$this->has('actions')) {
            return null;
        }
        $actions = $this->get('actions');
        $return  = [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                $return[] = [
                    'type'            => $action['type'],
                    'value'           => $action['value'],
                    'active'          => $this->convertBoolean((string)($action['active'] ?? 'false')),
                    'stop_processing' => $this->convertBoolean((string)($action['stop_processing'] ?? 'false')),
                ];
            }
        }

        return $return;
    }

    /**
     * @return array|null
     */
    private function getRuleTriggers(): ?array
    {
        if (!$this->has('triggers')) {
            return null;
        }
        $triggers = $this->get('triggers');
        $return   = [];
        if (is_array($triggers)) {
            foreach ($triggers as $trigger) {
                $return[] = [
                    'type'            => $trigger['type'],
                    'value'           => $trigger['value'],
                    'active'          => $this->convertBoolean((string)($trigger['active'] ?? 'false')),
                    'stop_processing' => $this->convertBoolean((string)($trigger['stop_processing'] ?? 'false')),
                ];
            }
        }

        return $return;
    }
}
