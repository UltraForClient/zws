<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends Controller
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
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        $tasks = $this->em->getRepository(Task::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route("/admin/users", name="admin_user")
     */
    public function users()
    {
        $users = $this->em->getRepository(User::class)->findAll();
        array_shift($users);
        return $this->render('admin/listClient.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="admin_user_one")
     */
    public function user(User $user)
    {
        $tasks = $this->em->getRepository(Task::class)->findByUserId($user->getId());

        return $this->render('admin/user.html.twig', [
            'user' => $user,
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route("/admin/task/{id}", name="admin_task_one")
     */
    public function task(Task $task)
    {
        return $this->render('admin/task.html.twig', [
            'task' => $task
        ]);
    }

    /**
     * @Route("/admin/valuation/{id}", name="valuation")
     */
    public function valuation(Task $task, Request $request)
    {
        $req = $request->request->all();

        if(count($req) > 0) {
            $task->setValuation($req['price']);

//            $this->sendMail($task->getUser()->getEmail(), $req);

            $this->em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/valuation.html.twig', [
            'task' => $task
        ]);
    }

    /**
     * @Route("admin/delete/task/{id}", name="remove_task")
     */
    public function removeTask(Task $task)
    {
        $task->setDeletedAt(new \DateTime());
        $this->em->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("admin/delete/user/{id}", name="remove_user")
     */
    public function removeUser(User $user)
    {
        $user->setDeletedAt(new \DateTime());
        $tasks = $this->em->getRepository(Task::class)->findByUserId($user->getId());
        foreach($tasks as $task) {
            $task = $this->em->getRepository(Task::class)->find($task['id']);
            $task->setDeletedAt(new \DateTime());
        }

        $this->em->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("admin/change-password", name="change_password_admin")
     */
    public function changePassword(Request $request)
    {
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

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/changePassword.html.twig', [
            'tasks' => false,
            'error' => $error
        ]);
    }

    private function sendMail($email, array $param)
    {
        $message = (new \Swift_Message('Zakład wiercenia Studziennych'))
            ->setFrom(getenv('MAILER_URL'))
            ->setTo($email)
            ->setBody(
                $this->renderView('email/valuation.html.twig', [
                    'price' => $param['price'],
                    'date' => $param['date'],
                    'comments' => $param['comments']
                ]),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
