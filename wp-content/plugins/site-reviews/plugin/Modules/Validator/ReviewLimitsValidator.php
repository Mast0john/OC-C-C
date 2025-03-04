<?php

namespace GeminiLabs\SiteReviews\Modules\Validator;

use GeminiLabs\SiteReviews\Helper;

class ReviewLimitsValidator extends ValidatorAbstract
{
    /**
     * @return string
     */
    public function filterSqlClauseOperator()
    {
        return 'AND';
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $method = Helper::buildMethodName(glsr_get_option('submissions.limit'), 'validateBy');
        return method_exists($this, $method)
            ? call_user_func([$this, $method])
            : true;
    }

    /**
     * @return void
     */
    public function performValidation()
    {
        if (!$this->isValid()) {
            $this->setErrors(__('You have already submitted a review.', 'site-reviews'));
        }
    }

    /**
     * @param string $value
     * @param string $whitelist
     * @return bool
     */
    protected function isWhitelisted($value, $whitelist)
    {
        if (empty($whitelist)) {
            return false;
        }
        $values = array_filter(array_map('trim', explode("\n", $whitelist)));
        return in_array($value, $values);
    }

    /**
     * @return bool
     */
    protected function validateByEmail()
    {
        glsr_log()->debug('Email is: '.$this->request->email);
        return $this->validateLimit('email', $this->request->email, [
            'email' => $this->request->email,
        ]);
    }

    /**
     * @return bool
     */
    protected function validateByIpAddress()
    {
        glsr_log()->debug('IP Address is: '.$this->request->ip_address);
        return $this->validateLimit('ip_address', $this->request->ip_address, [
            'ip_address' => $this->request->ip_address,
        ]);
    }

    /**
     * @return bool
     */
    protected function validateByUsername()
    {
        $user = wp_get_current_user();
        if (!$user->exists()) {
            return true;
        }
        glsr_log()->debug('Username is: '.$user->user_login);
        return $this->validateLimit('username', $user->user_login, [
            'status' => 'all',
            'author_id' => $user->ID,
        ]);
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    protected function validateLimit($key, $value, array $queryArgs)
    {
        if (empty($value) 
            || $this->isWhitelisted($value, glsr_get_option('submissions.limit_whitelist.'.$key))) {
            return true;
        }
        $queryArgs['assigned_posts'] = $this->request->assigned_posts;
        add_filter('query/sql/clause/operator', [$this, 'filterSqlClauseOperator']);
        $reviews = glsr_get_reviews($queryArgs);
        remove_filter('query/sql/clause/operator', [$this, 'filterSqlClauseOperator']);
        $result = 0 === $reviews->total;
        return glsr()->filterBool('validate/review-limits', $result, $reviews, $this->request, $key);
    }
}
