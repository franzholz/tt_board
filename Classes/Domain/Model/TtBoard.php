<?php // Auto generated on '2025-10-03T11:02:23+02:00'! Do NOT edit !!!
namespace JambageCom\TtBoard\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * TtBoard
 */
class TtBoard extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    /**
     * hidden
     * @var boolean
     */
    protected $hidden;

    /**
     * crdate
     * @var string
     */
    protected $crdate;

    /**
     * tstamp
     * @var string
     */
    protected $tstamp;

    /**
     * subject
     * @var string
     */
    protected $subject;

    /**
     * subject addition
     * @var string
     */
    protected $subjectAddition;

    /**
     * message
     * @var string
     */
    protected $message;

    /**
     * author
     * @var string
     */
    protected $author;

    /**
     * city
     * @var string
     */
    protected $city;

    /**
     * email
     * @var string
     */
    protected $email;

    /**
     * parent
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JambageCom\TtBoard\Domain\Model\TtBoard>
     */
    protected $parent = null;

    /**
     * notify_me
     * @var boolean
     */
    protected $notifyMe;

    /**
     * cr_ip
     * @var string
     */
    protected $crIp;

    /**
     * reference
     * @var string
     */
    protected $reference;

    /**
     * slug
     * @var string
     */
    protected $slug;


    /**
     * @return string
     */
    public function getCrdate() {
        return $this->crdate;
    }

    /**
     * @param string $crdate
     * @return void
     */
    public function setCrdate($crdate) {
        $this->crdate = $crdate;
    }

    /**
     * @return string
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * @param string $tstamp
     * @return void
     */
    public function setTstamp($tstamp) {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return void
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubjectAddition() {
        return $this->subjectAddition;
    }

    /**
     * @param string $subjectAddition
     * @return void
     */
    public function setSubjectAddition($subjectAddition) {
        $this->subjectAddition = $subjectAddition;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * @param string $author
     * @return void
     */
    public function setAuthor($author) {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Returns the parent
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JambageCom\TtBoard\Domain\Model\TtBoard>
     * $parent
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Sets the parent
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SICOR\SicAddress\Domain\Model\Category> $categories
     * @return void
     */
    public function setParent(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $parent) {
        $this->parent = $parent;
    }

    /**
     * @param int $uid
     * @return void
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }

    /**
     * @return bool
     */
    public function isHidden() {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function isNotifyMe() {
        return $this->notifyMe;
    }

    /**
     * @param bool $notifyMe
     */
    public function setNotifyMe($notifyMe) {
        $this->notifyMe = $notifyMe;
    }

    /**
     * @return string
     */
    public function getCrIp() {
        return $this->crIp;
    }

    /**
     * @param string $crIp
     * @return void
     */
    public function setCrIp($crIp) {
        $this->crIp = $crIp;
    }

    /**
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return void
     */
    public function setReference($reference) {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return void
     */
    public function setSlug($slug) {
        $this->slug = $slug;
    }
}

