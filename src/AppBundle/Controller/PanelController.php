<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PanelController extends Controller
{
    private $passwordEncoder;
    private $em;
    private $mailer;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, \Swift_Mailer $mailer)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/panel/profile", name="panel")
     */
    public function index()
    {
        if($this->getUser()->getRoles()[0] === 'ROLE_ADMIN') {
            return $this->redirectToRoute('admin');
        }

        return $this->render('panel/index.html.twig', [
            'tasks' => false
        ]);
    }

    /**
     * @Route("/panel", name="tasks")
     */
    public function tasks(Request $request)
    {
        $user = $this->getUser();

        if($user->getRoles()[0] === 'ROLE_ADMIN') {
            return $this->redirectToRoute('admin');
        }

        $tasks = $this->em->getRepository(Task::class)->findByUserId($user->getId());
        $req = $request->request->all();
        if(count($req) > 0) {
            $task = new Task();
            $task->setUser($user);
            if($request->files->get('task_imageFile')) {
                $task->setImageFile($request->files->get('task_imageFile'));
            }

            $form = $this->createForm(TaskType::class, $task);
            $form->submit($req);

            $this->em->persist($task);
            $this->em->flush();

            $tasks = $this->em->getRepository(Task::class)->findByUserId($user->getId());

            $this->sendMail($user->getEmail(), '', 'email2.html.twig');

            return $this->render('panel/tasks.html.twig', [
                'tasks' => $tasks
            ]);
        }


        return $this->render('panel/tasks.html.twig', [
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route("/panel/task/{id}", name="task_one")
     */
    public function task(Task $task)
    {
        return $this->render('panel/task.html.twig', [
            'task' => $task,
            'tasks' => null
        ]);
    }

    /**
     * @Route("/panel/task/edit/{id}", name="task_edit")
     */
    public function edit(Task $task, Request $request)
    {
        $req = $request->request->all();

        if(count($req) > 0) {
            $task->setImageFile($request->files->get('task_imageFile'));

            $formTask = $this->createForm(TaskType::class, $task);

            $formTask->submit($req);

            $task->setUser($this->getUser());

            $this->em->persist($task);

            $this->em->flush();


            return $this->redirectToRoute('tasks');
        }


        return $this->render('panel/edit.html.twig', [
            'task' => $task,
            'tasks' => null
        ]);
    }

    /**
     * @Route("/panel/change-password", name="change_password")
     */
    public function changePassword(Request $request)
    {
        if($this->getUser()->getRoles()[0] === 'ROLE_ADMIN') {
            return $this->redirectToRoute('admin');
        }

        $password = $request->request->get('password');
        $rePassword = $request->request->get('rePassword');

        $error = false;

        if($password !== $rePassword) {
            $error = true;
        }

        if($password && !$error) {
            $user = $this->getUser();

            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

            $this->em->flush();

            return $this->redirectToRoute('panel');
        }

        return $this->render('panel/changePassword.html.twig', [
            'tasks' => false,
            'error' => $error
        ]);
    }

    private function sendMail($email, $password, $template)
    {
        $message = (new \Swift_Message('Zakład wiercenia Studziennych'))
            ->setFrom(getenv('MAILER_URL'))
            ->setTo($email)
            ->setBody(
                $this->renderView($template, [
                    'email' => $email,
                    'password' => $password
                ]),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
