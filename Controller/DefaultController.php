<?php

namespace Soloist\Bundle\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Soloist\Bundle\BlogBundle\Entity\Post;

use Pagerfanta\Adapter\DoctrineORMAdapter,
    Pagerfanta\Pagerfanta;

class DefaultController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction($page = 1)
    {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('SoloistBlogBundle:Post');

        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->getQuery();

        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);

        $pager->setMaxPerPage($this->container->getParameter('soloist_blog_max_per_page'));

        try {
            $pager->setCurrentPage($page, false, true);
        } catch(\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException('Invalid page number.');
        }

        return array(
            'pager' => $pager
        );
    }

    /**
     * @Template()
     */
    public function showAction(Post $post)
    {
        return array('post' => $post);
    }

    /**
     * @Template()
     */
    public function showLeadAction()
    {
        try {
            return array(
                'post' => $this->getDoctrine()->getEntityManager()->getRepository('SoloistBlogBundle:Post')->findLead()
            );
        } catch(\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }

    /**
     * @Template()
     */
    public function showLastsAction($nb = 5)
    {
        return array(
            'posts' => $this->getDoctrine()->getManager()->getRepository('SoloistBlogBundle:Post')->findLasts($nb)
        );
    }

    /**
     * Show a list of posts
     * @param  string $category
     * @return array
     * @Template()
     */
    public function showByCategoryAction($category, $limit = 5)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('SoloistBlogBundle:Post')->findByCategory($category, $limit);

        return array(
            'posts' => $posts
        );
    }
}
