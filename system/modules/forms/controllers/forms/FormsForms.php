<?php
class FormsForms {

    public function contact($formValues) {
        $email = new Message();
        $email->from($formValues->contactEmail, $formValues->contactName);
        $email->to('contact@RentScore.com');
        $email->subject('[RentScore Contact Form] Message from '.$formValues->contactName);
        $email->message($formValues->contactMessage);

        if($email->send()) {
            return array('successPageHtml' => '
                <h2>Message Sent</h2>
                <p>Thanks for contacting us, we will respond as soon as possible.</p>
            ');
        }
        else {
            return array('failureNoticeHtml' => 'We apologize, there was a proplem sending your message through this contact form. You may write us an e-mail directly at <a href="mailto:contact@RentScore.com">contact@RentScore.com</a>.');
        }
    }

    public function contactDialog($formValues) {
        return $this->contact($formValues);
    }

}
?>