<?php
/**
 * @file classes/security/authorization/WorkflowStageAccessPolicy.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowStageAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's submission workflow stage components
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');
import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

abstract class PKPWorkflowStageAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $submissionParameterName string
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 */
	function PKPWorkflowStageAccessPolicy($request, &$args, $roleAssignments, $submissionParameterName = 'submissionId', $stageId) {
		parent::ContextPolicy($request);

		// A workflow stage component requires a valid workflow stage.
		import('lib.pkp.classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));

		// A workflow stage component can only be called if there's a
		// valid submission in the request.
		import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
		$this->addPolicy(new SubmissionRequiredPolicy($request, $args, $submissionParameterName));

		$this->_addUserAccessibleWorkflowStageRequiredPolicy($request);

		// Users can access all whitelisted operations for submissions and workflow stages...
		$roleBasedPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		foreach ($roleAssignments as $roleId => $operations) {
			$roleBasedPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $roleId, $operations));
		}
		$this->addPolicy($roleBasedPolicy);

		// ... if they can access the requested workflow stage.
		import('lib.pkp.classes.security.authorization.internal.UserAccessibleWorkflowStagePolicy');
		$this->addPolicy(new UserAccessibleWorkflowStagePolicy($stageId));
	}

	/**
	 * Get the user-accessible workflow stage policy for this application
	 * @param $request PKPRequest
	 * @return UserAccessibleWorkflowStageRequiredPolicy
	 */
	abstract protected function _addUserAccessibleWorkflowStageRequiredPolicy($request);
}

?>
