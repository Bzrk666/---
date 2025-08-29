<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UnsubscribeController extends AbstractController
{
	public function form(): Response
	{
		$companies = [
			['slug' => 'mfo-example-1', 'name' => 'ООО МФО Пример 1', 'address' => '101000, г. Москва, ул. Примерная, д. 1'],
			['slug' => 'mfo-example-2', 'name' => 'ООО МФО Пример 2', 'address' => '190000, г. Санкт-Петербург, Невский пр., д. 2'],
		];

		return $this->render('unsubscribe/form.html.twig', [
			'companies' => $companies,
		]);
	}

	public function generate(Request $request): Response
	{
		$lastName        = trim((string) $request->request->get('last_name'));
		$firstName       = trim((string) $request->request->get('first_name'));
		$middleName      = trim((string) $request->request->get('middle_name'));
		$email           = trim((string) $request->request->get('email'));
		$phone           = trim((string) $request->request->get('phone_number'));
		$contractNumber  = trim((string) $request->request->get('contract_number'));
		$statementDate   = trim((string) $request->request->get('statement_date'));
		$companySlug     = trim((string) $request->request->get('company_slug'));
		$consent         = (bool) $request->request->get('consent');
		$signatureDataUrl= (string) $request->request->get('signature_data_url');

		if (!$lastName || !$firstName || !$email || !$phone || !$contractNumber || !$statementDate || !$companySlug || !$consent || !$signatureDataUrl) {
			return new Response('Некорректные данные формы', 400);
		}

		$companies = [
			'mfo-example-1' => ['name' => 'ООО МФО Пример 1', 'address' => '101000, г. Москва, ул. Примерная, д. 1'],
			'mfo-example-2' => ['name' => 'ООО МФО Пример 2', 'address' => '190000, г. Санкт-Петербург, Невский пр., д. 2'],
		];
		$company = $companies[$companySlug] ?? null;
		if (!$company) {
			return new Response('Неизвестная МФО', 400);
		}

		if (strpos($signatureDataUrl, 'data:image/png;base64,') !== 0) {
			return new Response('Некорректная подпись', 400);
		}

		$fullName = trim($lastName . ' ' . $firstName . ' ' . ($middleName ?? ''));

		$html = $this->renderView('unsubscribe/pdf.html.twig', [
			'company'          => $company,
			'fullName'         => $fullName,
			'email'            => $email,
			'phone'            => $phone,
			'contractNumber'   => $contractNumber,
			'statementDate'    => $statementDate,
			'signatureDataUrl' => $signatureDataUrl,
		]);

		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		return new Response($dompdf->output(), 200, [
			'Content-Type' => 'application/pdf',
			'Content-Disposition' => 'attachment; filename="otpiska-' . $companySlug . '.pdf"',
		]);
	}
}



