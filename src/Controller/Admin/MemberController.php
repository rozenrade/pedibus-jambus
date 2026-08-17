<?php
// src/Controller/Admin/MemberController.php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/members')]
class MemberController extends AbstractController
{
    #[Route('/', name: 'admin_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('admin/member/index.html.twig', [
            'groupedMembers' => $memberRepository->findAllGrouped(),
            'categories' => Member::CATEGORIES,
        ]);
    }

    #[Route('/new', name: 'admin_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, MemberRepository $memberRepository): Response  
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $member = new Member();

        if ($category = $request->query->get('category')) {
            $member->setCategory($category);
        }

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member->setPosition($memberRepository->getNextPosition($member->getCategory()));

            $em->persist($member);
            $em->flush();

            $this->addFlash('success', 'Le membre a bien été ajouté.');

            return $this->redirectToRoute('admin_member_index');
        }

        return $this->render('admin/member/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_member_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Member $member, EntityManagerInterface $em): Response   
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les modifications ont bien été enregistrées.');

            return $this->redirectToRoute('admin_member_index');
        }

        return $this->render('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_member_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Member $member, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('delete-member-' . $member->getId(), $request->request->get('_token'))) {
            $em->remove($member);
            $em->flush();

            $this->addFlash('success', 'Le membre a bien été supprimé.');
        }

        return $this->redirectToRoute('admin_member_index');
    }

    #[Route('/reorder', name: 'admin_member_reorder', methods: ['POST'])]
    public function reorder(Request $request, EntityManagerInterface $em, MemberRepository $memberRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['success' => false], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!$this->isCsrfTokenValid('reorder-members', $data['_token'] ?? '')) {
            return $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $orderedIds = $data['order'] ?? [];

        foreach ($orderedIds as $index => $memberId) {
            $member = $memberRepository->find($memberId);
            if ($member) {
                $member->setPosition($index);
            }
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

}
