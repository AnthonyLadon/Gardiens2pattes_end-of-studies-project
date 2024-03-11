<?php

namespace App\Controller;

use App\Entity\Maitres;
use App\Entity\Adresses;
use App\Entity\Prestataires;
use App\Entity\Utilisateurs;
use App\Security\EmailVerifier;
use OpenCage\Geocoder\Geocoder;
use App\Security\AppAuthenticator;
use Symfony\Component\Mime\Address;
use App\Form\RegistrationMaitreFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RegistrationGardienFormType;
use App\Repository\UtilisateursRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }


    // -------------------------------------------------
    // Choix du type d'inscription
    // -------------------------------------------------
    /**
     * @Route("/inscription",name="register_choix")
     */
    public function registerChoice(): Response
    {
        return $this->render('registration/registration_choix.html.twig');
    }


    // ----------------------------------------------------
    // Choix du type d'inscription (si inscrit via Google)
    // ----------------------------------------------------
    /**
     * @Route("/choix_role/{id}",name="choix_role")
     */
    public function choixRole($id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateurs::class)->find($id);
        $this->addFlash('notice', 'Vous devez définir votre rôle avant de pouvoir utiliser votre compte');

        return $this->render('registration/choix_role.html.twig', [
            'user' => $user,
        ]);
    }

    // -------------------------------------------------------
    // Ajout ROLE_MAITRE à l'utilisateur (inscrit via Google)
    // -------------------------------------------------------
    /**
     * @Route("/ajout_role_maitre/{id}",name="ajout_role_maitre")
     */
    public function ajoutRoleMaitre($id, EntityManagerInterface $entityManager, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $maitre = new Maitres();
        $user = $entityManager->getRepository(Utilisateurs::class)->find($id);
        $user->setRoles(['ROLE_MAITRE']);
        $user->setMaitre($maitre);
        $entityManager->persist($user);

        // Pour rester connecté après le flush
        $userId = $user->getId(); // Récupérer l'identifiant de l'utilisateur
        // Stocker l'identifiant de l'utilisateur dans la session
        $session->set('user_id', $userId);

        $entityManager->flush();
        $userId = $session->get('user_id'); // Restaurer l'identifiant de l'utilisateur après le flush
        // Récupérer l'utilisateur à partir de l'identifiant
        $user = $entityManager->getRepository(Utilisateurs::class)->find($userId);
        // Ré-authentifier l'utilisateur sans déconnexion
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $tokenStorage->setToken($token);

        $this->addFlash('notice', 'Votre rôle de maitre a bien été défini. Bienvenue!');

        return $this->redirectToRoute('profil_maitre', [
            'id' => $maitre->getId(),
        ]);
    }


    // -------------------------------------------------------
    // Ajout ROLE_GARDIEN à l'utilisateur (inscrit via Google)
    // -------------------------------------------------------
    /**
     * @Route("/ajout_role_gardien/{id}",name="ajout_role_gardien")
     */
    public function ajoutRoleGardien($id, EntityManagerInterface $entityManager, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $gardien = new Prestataires();
        $user = $entityManager->getRepository(Utilisateurs::class)->find($id);
        $user->setRoles(['ROLE_GARDIEN']);
        $user->setPrestataire($gardien);
        $entityManager->persist($user);

        // Pour rester connecté après le flush
        $userId = $user->getId(); // Récupérer l'identifiant de l'utilisateur
        // Stocker l'identifiant de l'utilisateur dans la session
        $session->set('user_id', $userId);

        $entityManager->flush();
        $userId = $session->get('user_id'); // Restaurer l'identifiant de l'utilisateur après le flush
        // Récupérer l'utilisateur à partir de l'identifiant
        $user = $entityManager->getRepository(Utilisateurs::class)->find($userId);
        // Ré-authentifier l'utilisateur sans déconnexion
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $tokenStorage->setToken($token);

        $this->addFlash('notice', 'Votre rôle de gardien a bien été défini. Bienvenue!');

        return $this->redirectToRoute('profil_gardien', [
            'id' => $gardien->getId(),
        ]);
    }




    // -------------------------------------------------
    // Inscription en tant que Maître
    // -------------------------------------------------
    /**
     * @Route("/inscription/maitre",name="register_maitre")
     */
    public function registerMaitre(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, TranslatorInterface $translator): Response
    {
        $user = new Utilisateurs();
        $adresse = new Adresses();
        $maitre = new Maitres();

        $formUser = $this->createForm(RegistrationMaitreFormType::class, $user);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {

            // verification que l'utilisateur ait bien entré un numéro de tel belge
            $tel = $formUser->get("telNum")->getViewData();
            if (preg_match("/^0[1-9]([-. ]?[0-9]{2}){4}$/", $tel)) {
                $user->setTelNum($tel);
            } else {
                $user->setTelNum(null);
                $this->addFlash('notice', 'Veuillez entrer un numéro de téléphone belge valide');
            }

            // récupération données communes,localité,zipCodes depuis fichier json
            $json = file_get_contents('../public/zipcode-belgium.json');
            $data = json_decode($json, true);

            // ajout commune selon le code postal entré par l'utilisateur
            $codePostal = $formUser->get("CodePostal")->getViewData();
            $commune = $formUser->get("commune")->getViewData();
            $localite = null;
            $rue = $formUser->get("rue")->getViewData();
            $num = $formUser->get("numero")->getViewData();

            // verifie que $codePostal est bien un Integer de 4 chiffres
            // sinon ajout un 0 devant
            $codePostal = (int)$codePostal;
            $codePostal = strval($codePostal);
            $codePostal = str_pad($codePostal, 4, "0", STR_PAD_LEFT);

            // Trouve la localité selon code postal entrée par l'utilisateur
            foreach ($data as $key => $value) {
                if ($value['zip'] == $codePostal) {
                    $localite = $value['localite'];
                } else {
                    $localite = null;
                }
            }

            // récupération des coordonnées GPS de l'adresse de l'utilisateur
            $lat = null;
            $lng = null;

            $adresseString = ($rue . " " . $num . " " . $codePostal . " " . $commune);
            $apiKey = 'b091b28cf9ff4f33acbedc0c90166f8c'; // <- Clé API OpenCage
            // Appel de l'API Geocoder (opencagedata.com) pour récupérer les coordonnées GPS de l'adresse
            $geocoder = new Geocoder($apiKey);

            $result = $geocoder->geocode($adresseString);
            if ($result) {
                // récupération des données de latitude et longitude de l'adresse passée en paramêtre
                if (isset($result['results'][0]['geometry']['lat']) && isset($result['results'][0]['geometry']['lng'])) {
                    $lat = $result['results'][0]['geometry']['lat'];
                    $lng = $result['results'][0]['geometry']['lng'];


                    // remplissage entiée adresse et ajout à l'entité user
                    $adresse->setCodePostal($codePostal);
                    $adresse->setCommune($commune);
                    if ($localite != null) {
                        // si la localité est trouvée dans le fichier json
                        $adresse->setLocalite($localite);
                    }
                    $adresse->setRue($rue);
                    $adresse->setNumero($num);
                    $adresse->setLatitude($lat);
                    $adresse->setLongitude($lng);


                    // remplissage champs entité user 
                    $user->setAdresse($adresse);
                    $user->setDateInscription(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                    $user->setEmail($formUser->get("email")->getViewData());
                    $user->setRoles(['ROLE_MAITRE']);
                    $user->setNom($formUser->get("nom")->getViewData());
                    $user->setPrenom($formUser->get("prenom")->getViewData());
                    $user->setPseudo($formUser->get("pseudo")->getViewData());
                    $formUser->get("telNum")->getViewData() != null ? $user->setTelNum($formUser->get("telNum")->getViewData()) : $user->setTelNum(null);
                    $user->setMaitre($maitre);


                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $formUser->get('plainPassword')->getData()
                        )
                    );

                    $user->setMaitre($maitre);
                    $maitre->setUtilisateur($user);
                    $user->setDateInscription(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                    if ($formUser->get('newsLetter')->getData() == true) {
                        $maitre->setNewsLetter(true);
                    }
                    $maitre->setBio($formUser->get('bio')->getData());
                    $entityManager->persist($user);
                    $entityManager->persist($maitre);
                    $entityManager->persist($adresse);
                    $entityManager->flush();

                    $msg = $translator->trans('Votre inscription a bien été prise en compte');
                    $this->addFlash('success', $msg);

                    // Generate a signed url and email it to the user
                    $this->emailVerifier->sendEmailConfirmation(
                        'app_verify_email',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address('admin@gardiensapattes.be', 'Gardiens à 2 pattes'))
                            ->to($user->getEmail())
                            ->subject('Veuillez confirmer votre adrese email')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                    );

                    return $userAuthenticator->authenticateUser(
                        $user,
                        $authenticator,
                        $request
                    );
                } else {
                    $this->addFlash('notice', 'Adresse non trouvée, veuillez verifier votre adresse.');
                    return $this->redirectToRoute('register_maitre');
                }
            }
            return $this->redirectToRoute('home');
        }
        return $this->render('registration/maitre_registration.html.twig', [
            'registrationForm' => $formUser->createView(),
        ]);
    }


    // -------------------------------------------------
    // Inscription en tant que Gardien
    // -------------------------------------------------
    /**
     * @Route("/inscription/gardien",name="register_gardien")
     */
    public function registerGardien(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, TranslatorInterface $translator): Response
    {

        $user = new Utilisateurs();
        $gardien = new Prestataires();
        $adresse = new Adresses();

        $formUser = $this->createForm(RegistrationGardienFormType::class, $user);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {

            // verification que l'utilisateur ait bien entré un numéro de tel belge
            $tel = $formUser->get("telNum")->getViewData();
            if (preg_match("/^0[1-9]([-. ]?[0-9]{2}){4}$/", $tel)) {
                $user->setTelNum($tel);
            } else {
                $user->setTelNum(null);
                $this->addFlash('notice', 'Veuillez entrer un numéro de téléphone belge valide');
            }

            // récupération données communes,localité,zipCodes depuis fichier json
            $json = file_get_contents('../public/zipcode-belgium.json');
            $data = json_decode($json, true);

            // ajout commune selon le code postal entré par l'utilisateur
            $codePostal = $formUser->get("CodePostal")->getViewData();
            $commune = $formUser->get("commune")->getViewData();
            $localite = null;
            $rue = $formUser->get("rue")->getViewData();
            $num = $formUser->get("numero")->getViewData();

            // verifie que $codePostal est bien un Integer de 4 chiffres
            // sinon ajout un 0 devant
            $codePostal = (int)$codePostal;
            $codePostal = strval($codePostal);
            $codePostal = str_pad($codePostal, 4, "0", STR_PAD_LEFT);

            // Trouve la localité selon code postal entrée par l'utilisateur
            foreach ($data as $key => $value) {
                if ($value['zip'] == $codePostal) {
                    $localite = $value['localite'];
                } else {
                    $localite = null;
                }
            }

            // récupération des coordonnées GPS de l'adresse de l'utilisateur
            $lat = null;
            $lng = null;

            $adresseString = ($rue . " " . $num . " " . $codePostal . " " . $commune);
            $apiKey = 'b091b28cf9ff4f33acbedc0c90166f8c'; // <- Clé API OpenCage
            // Appel de l'API Geocoder (opencagedata.com) pour récupérer les coordonnées GPS de l'adresse
            $geocoder = new Geocoder($apiKey);

            $result = $geocoder->geocode($adresseString);
            if ($result) {
                // récupération des données de latitude et longitude de l'adresse passée en paramêtre
                if (isset($result['results'][0]['geometry']['lat']) && isset($result['results'][0]['geometry']['lng'])) {
                    $lat = $result['results'][0]['geometry']['lat'];
                    $lng = $result['results'][0]['geometry']['lng'];


                    // remplissage entiée adresse et ajout à l'entité user
                    $adresse->setCodePostal($codePostal);
                    $adresse->setCommune($commune);
                    if ($localite != null) {
                        $adresse->setLocalite($localite);
                    }
                    $adresse->setRue($rue);
                    $adresse->setNumero($num);
                    $adresse->setLatitude($lat);
                    $adresse->setLongitude($lng);

                    // remplissage champs entité user 
                    $user->setAdresse($adresse);
                    $user->setDateInscription(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                    $user->setEmail($formUser->get("email")->getViewData());
                    $user->setRoles(['ROLE_GARDIEN']);
                    $user->setNom($formUser->get("nom")->getViewData());
                    $user->setPrenom($formUser->get("prenom")->getViewData());
                    $user->setPseudo($formUser->get("pseudo")->getViewData());
                    $user->setTelNum($formUser->get("telNum")->getViewData());
                    $user->setPrestataire($gardien);

                    // remplissage champs entité prestataire
                    $gardien->setBio($formUser->get('bio')->getData());


                    // encodage du mot de passe
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $formUser->get('plainPassword')->getData()
                        )
                    );

                    // ajout des entités à la base de données
                    $entityManager->persist($adresse);
                    $entityManager->persist($user);
                    $entityManager->persist($gardien);
                    $entityManager->flush();

                    $msg = $translator->trans('Votre inscription a bien été prise en compte');
                    $this->addFlash('success', $msg);

                    // generate a signed url and email it to the user
                    $this->emailVerifier->sendEmailConfirmation(
                        'app_verify_email',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address('admin@gardiens2pattes.be', 'Gardiens à 2 pattes'))
                            ->to($user->getEmail())
                            ->subject('Veuillez confirmer votre adrese email')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                    );

                    return $userAuthenticator->authenticateUser(
                        $user,
                        $authenticator,
                        $request
                    );
                } else {
                    $this->addFlash('notice', 'Adresse non trouvée, veuillez verifier votre adresse.');
                    return $this->redirectToRoute('register_gardien');
                }
                return $this->redirectToRoute('home');
            }
            return $this->redirectToRoute('home');
        }
        return $this->render('registration/gardien_registration.html.twig', [
            'registrationForm' => $formUser->createView(),
        ]);
    }


    // -------------------------------------------------
    // Envoi email vérification
    // -------------------------------------------------
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmailuser(Request $request, UtilisateursRepository $utilisateursRepository, TranslatorInterface $translator): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $utilisateursRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre email a bien été vérifié');

        return $this->redirectToRoute('home');
    }



    // -------------------------------------------------------------------
    // Ajout d'un ADMIN en base de données (si aucun admin n'existe)
    // email: admin@gardiens2pattes.be
    // password: admin -> à modifer après ajout depuis l'interface admin
    // -------------------------------------------------------------------
    /**
     * @Route("/ajout_admin",name="ajoutAdmin")
     */

    public function ajoutAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {

        // verifier si un admin existe déjà (email)
        $admin = $entityManager->getRepository(Utilisateurs::class)->findOneBy(['email' => 'admin@gardiens2pattes.be']);

        if ($admin != null) {

            $msg = $translator->trans('L\'admin principal a déjà été créé');
            $this->addFlash('error', $msg);
            return $this->redirectToRoute('home');
        }

        $admin = new Utilisateurs();
        $admin->setEmail('admin@gardiens2pattes.be');
        $admin->setPassword('admin');
        $admin->setDateInscription(new \DateTime());
        $admin->setNom('admin');
        $admin->setPrenom('admin');
        $admin->setPseudo('admin');
        $admin->setIsVerified(true);
        $admin->setBanni(0);
        $admin->setRoles(["ROLE_ADMIN"]);

        $plaintextPassword = 'admin';

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $admin,
            $plaintextPassword
        );
        $admin->setPassword($hashedPassword);

        $entityManager->persist($admin);
        $entityManager->flush();

        $msg = $translator->trans('Un admin a bien été créé : admin@gardiens2pattes.be, mot de passe: "admin" /!\ Changez le mot de passe /!\ ');
        $this->addFlash('success', $msg);


        return $this->redirectToRoute('home');
    }
}
