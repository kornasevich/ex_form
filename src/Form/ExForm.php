<?php

namespace Drupal\ex_form\Form;

use Drupal\Core\Form\FormBase;                                                                            // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface;                                                        // Класс отвечает за обработку данных

/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class ExForm extends FormBase
{

    // метод, который отвечает за саму форму - кнопки, поля
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['firstName'] = [
            '#type' => 'textfield',
            '#title' => $this->t('First Name'),
            '#required' => TRUE,
        ];
        $form['lastName'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Last Name'),
            '#required' => TRUE,
        ];

        $form['subject'] = [
            '#type' => 'textfield',
            '#title' => $this->t(' Subject'),
            '#required' => TRUE,
        ];

        $form['message'] = [
            '#type' => 'textarea',
            '#title' => $this->t(' Message'),
            '#required' => TRUE,
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => $this->t('E-mail'),
            '#required' => TRUE,
        ];

        // Add a submit button that handles the submission of the form.
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Отправить форму'),
        ];

        return $form;
    }

    // метод, который будет возвращать название формы
    public function getFormId()
    {
        return 'ex_form_exform_form';
    }

    public function validateForm(array &$form,FormStateInterface $form_state){
        $mail = $form_state->getValue('email');
        if(filter_var($mail, FILTER_VALIDATE_EMAIL) === FALSE){

            $form_state->setErrorByName('email','Проверьте корректность ввода почты!');

        }

    }

    public function submitForm(array &$form,FormStateInterface $form_state){

        $message = $form_state->getValue('message');

        $message = wordwrap($message,70,"\r\n");

        $subject = $form_state->getValue('subject');

        $res = mail('nkornasevich@mail.ru', $subject, $message);

        if($res){

            \Drupal::logger('my_form')->notice('Mail is sent. E-mail: '.$form_state->getValue('email'));

            drupal_set_message('Форма отправлена.');

        }

        $email = $form_state->getValue('email');
        $firstname = $form_state->getValue('firstName');
        $lastname = $form_state->getValue('lastName');


        $url = "https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/" .$email."/?hapikey=3982bf51-34f2-465a-83e8-18b31a831a59";

        $data = array(
            'properties' => [
                [
                    'property' => 'firstname',
                    'value' => $firstname
                ],
                [
                    'property' => 'lastname',
                    'value' => $lastname
                ]
            ]
        );


        $json = json_encode($data,true);

        $response = \Drupal::httpClient()->post($url.'&_format=hal_json', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $json
        ]);
    }
}
