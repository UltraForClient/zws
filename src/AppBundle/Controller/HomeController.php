<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\TaskType;
use AppBundle\Form\UserType;
use Password\Generator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends Controller
{
    private $passwordEncoder;
    private $passwordGenerator;
    private $mailer;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        $this->passwordEncoder = $passwordEncoder;

        $this->passwordGenerator = new Generator();
        $this->passwordGenerator->setMinLength(16);
        $this->passwordGenerator->setNumberOfUpperCaseLetters(2);
        $this->passwordGenerator->setNumberOfNumbers(2);
        $this->passwordGenerator->setNumberOfSymbols(1);

        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        if($user = $this->getUser()) {
            if($user->getRoles() === ['ROLE_ADMIN']) {
                return $this->redirectToRoute('admin');
            } elseif($user->getRoles() === ['ROLE_USER']) {
                return $this->redirectToRoute('tasks');
            }
        } else {
            return $this->redirectToRoute('form');
        }

        return $this->render('home/index.html.twig', [
        ]);
    }

    /**
     * @Route("/form", name="form")
     */
    public function form(Request $request)
    {
        $req = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if(count($req) > 0) {
            $data = $this->matchDataForm($req);

            $newUser = false;

            if(!$user = $em->getRepository(User::class)->findOneBy(['email' => $data['user']['email']])) {
                $user = new User();
                $newUser = true;
            }

            $task = new Task();

            if($request->files->get('task_imageFile')) {
                $task->setImageFile($request->files->get('task_imageFile'));
            }

            $formUser = $this->createForm(UserType::class, $user);
            $formTask = $this->createForm(TaskType::class, $task);


            $formUser->submit($data['user']);
            $formTask->submit($data['task']);

            if($newUser) {
                $password = $this->passwordGenerator->generate();
                $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

                $this->sendMail($data['user']['email'], $password, 'email.html.twig');
            } else {
                $this->sendMail($data['user']['email'], '', 'email2.html.twig');
            }


            $task->setUser($user);

            $em->persist($user);
            $em->persist($task);

            $em->flush();


            return $this->redirectToRoute('form_summary', [
                'email' => $data['user']['email']
            ]);
        }

        return $this->render('form/form.html.twig', [

        ]);
    }

    /**
     * @Route("/polityka-prywatnosci", name="policy")
     */
    public function policy()
    {
        return $this->render('home/policy.html.twig', [
        ]);
    }

    /**
     * @Route("/form/summary", name="form_summary")
     */
    public function formSummary(Request $request)
    {
        return $this->render('form/formSummary.html.twig', [
            'email' => $request->query->get('email')
        ]);
    }

    /**
     * @Route("/email", name="email")
     */
    public function email(Request $request)
    {
        return $this->render('email/email.html.twig', [
            'email' => 'email@gmail.com',
            'password' => 'asdasdasdasdasd'
        ]);
    }

    private function sendMail($email, $password, $template)
    {
        $message = (new \Swift_Message('Zakład wiercenia Studziennych'))
            ->setFrom('zlecenia@zws.com.pl ')
            ->setTo($email)
            ->setBody(
                $this->renderView('email/' . $template, [
                    'email' => $email,
                    'password' => $password
                ]),
                'text/html'
            );

        $this->mailer->send($message);
    }

    private function matchDataForm($data) {
        $dataTask = array_filter($data, function($key) {
            return strpos($key, 'task_') === 0;
        }, ARRAY_FILTER_USE_KEY);


        $data = array_filter($data, function($key) {
            return strpos($key, 'task_') !== 0;
        }, ARRAY_FILTER_USE_KEY);

        function removePrefix(array $input, $prefix) {

            $return = [];
            foreach ($input as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $key = substr($key, strlen($prefix));
                }

                if (is_array($value)) {
                    $value = removePrefix($value, $prefix);
                }

                $return[$key] = $value;
            }
            return $return;
        }

        $dataTask = removePrefix($dataTask, 'task_');

        return [
            'user' => $data,
            'task' => $dataTask
        ];
    }
}
