<?php

declare(strict_types=1);

namespace agoalofalife\DecomposeValidator;

interface ValidatorValue
{
    /**
     * Should return list rules
     * @example ['required','email','unique:users,email'];
     * @return array
     */
    public function getRules(): array;

    /**
     * @return string
     */
    public function getAttribute(): string;

    /**
     * @return array
     */
    public function getMessages(): array;
}
